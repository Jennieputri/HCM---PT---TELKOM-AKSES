$(function ()
{
    var $page = $('#dashboard-page');
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var numberFormatter = new Intl.NumberFormat('id-ID');
    var todayFormatter = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    var countUpDuration = 1100;
    var barStaggerDelay = 90;

    var employeeStatuses =
    [
        { key: 'active', label: 'Aktif', tone: 'good' },
        { key: 'pending', label: 'Menunggu Persetujuan', tone: 'warning' },
        { key: 'rejected', label: 'Ditolak', tone: 'critical' },
        { key: 'inactive', label: 'Nonaktif', tone: 'neutral' }
    ];

    function greetingForHour(hour)
    {
        if (hour < 11)
        {
            return 'Selamat pagi';
        }

        if (hour < 15)
        {
            return 'Selamat siang';
        }

        return hour < 18 ? 'Selamat sore' : 'Selamat malam';
    }

    function readSummaryValue(summary, path)
    {
        return path.split('.').reduce(function (value, key)
        {
            return value === null || value === undefined ? null : value[key];
        }, summary);
    }

    function percentage(part, total)
    {
        return total > 0 ? Math.round((part / total) * 100) : 0;
    }

    function animateCount(element, targetValue)
    {
        if (prefersReducedMotion)
        {
            element.textContent = numberFormatter.format(targetValue);

            return;
        }

        var startTime = null;

        function drawFrame(timestamp)
        {
            startTime = startTime || timestamp;

            var progress = Math.min((timestamp - startTime) / countUpDuration, 1);
            var easedProgress = 1 - Math.pow(1 - progress, 3);

            element.textContent = numberFormatter.format(Math.round(targetValue * easedProgress));

            if (progress < 1)
            {
                requestAnimationFrame(drawFrame);
            }
        }

        requestAnimationFrame(drawFrame);
    }

    function growTo(element, widthPercent, delay)
    {
        setTimeout(function ()
        {
            element.style.width = widthPercent + '%';
        }, prefersReducedMotion ? 0 : delay);
    }

    function renderCountUps(summary)
    {
        $page.find('[data-count-up]').each(function ()
        {
            var value = readSummaryValue(summary, $(this).data('count-up'));

            if (value !== null)
            {
                animateCount(this, Number(value));
            }
        });
    }

    function renderEmployeeStatus(employees)
    {
        var $bar = $('#employee-status-bar').empty();
        var $legend = $('#employee-status-legend').empty();
        var ariaParts = [];

        $.each(employeeStatuses, function (index, status)
        {
            var total = employees[status.key];
            var share = percentage(total, employees.total);

            ariaParts.push(status.label + ' ' + total);

            if (total > 0)
            {
                var segment = $('<span class="stacked-bar-segment"></span>').addClass('is-' + status.tone).attr('title', status.label + ': ' + total)[0];

                $bar.append(segment);
                growTo(segment, share, 150 + (index * barStaggerDelay));
            }

            $legend.append(
                $('<li class="status-legend-item"></li>').append(
                    $('<span class="status-legend-swatch" aria-hidden="true"></span>').addClass('is-' + status.tone),
                    $('<span class="status-legend-label"></span>').text(status.label),
                    $('<span class="status-legend-value"></span>').text(numberFormatter.format(total) + ' · ' + share + '%')
                )
            );
        });

        $bar.attr('aria-label', 'Komposisi status karyawan: ' + ariaParts.join(', '));
    }

    function createMeter(label, value, total, delay)
    {
        var share = percentage(value, total);
        var fill = $('<span class="meter-fill"></span>')[0];

        growTo(fill, share, delay);

        return $('<div class="meter"></div>').append(
            $('<div class="meter-header"></div>').append(
                $('<span class="meter-label"></span>').text(label),
                $('<span class="meter-value"></span>').text(numberFormatter.format(value) + ' / ' + numberFormatter.format(total) + ' · ' + share + '%')
            ),
            $('<div class="meter-track" role="meter" aria-valuemin="0"></div>')
                .attr({ 'aria-valuemax': total, 'aria-valuenow': value, 'aria-label': label })
                .append(fill)
        );
    }

    function renderAlatKerja(alatKerja)
    {
        $('#alat-kerja-meters').empty().append(
            createMeter('Kondisi baik', alatKerja.good_condition, alatKerja.total, 250),
            createMeter('Sudah dikembalikan', alatKerja.returned, alatKerja.total, 250 + barStaggerDelay)
        );
    }

    function renderStoKecamatan(rows)
    {
        var $list = $('#sto-kecamatan-bars').empty();
        var largestTotal = Math.max.apply(null, rows.map(function (row)
        {
            return row.total;
        }).concat([1]));

        if (rows.length === 0)
        {
            $list.append('<li class="dashboard-empty">Belum ada data STO.</li>');

            return;
        }

        $.each(rows, function (index, row)
        {
            var fill = $('<span class="bar-list-fill"></span>')[0];

            growTo(fill, percentage(row.total, largestTotal), 300 + (index * barStaggerDelay));

            $list.append(
                $('<li class="bar-list-item"></li>')
                    .attr('title', row.kecamatan + ': ' + row.total + ' STO')
                    .append(
                        $('<span class="bar-list-label"></span>').text(row.kecamatan),
                        $('<span class="bar-list-track"></span>').append(fill),
                        $('<span class="bar-list-value"></span>').text(numberFormatter.format(row.total))
                    )
            );
        });
    }

    function showLoadError()
    {
        $page.find('#employee-status-legend, #alat-kerja-meters, #sto-kecamatan-bars')
            .empty()
            .append('<p class="dashboard-empty">Data gagal dimuat. Muat ulang halaman.</p>');
    }

    $page.find('.reveal').each(function (index)
    {
        this.style.setProperty('--reveal-order', index);
    });

    $('#dashboard-today').text(todayFormatter.format(new Date()));
    $('#dashboard-salutation').text(greetingForHour(new Date().getHours()));

    $.post($page.data('summary-url'))
        .done(function (summary)
        {
            renderCountUps(summary);
            renderEmployeeStatus(summary.employees);
            renderAlatKerja(summary.alat_kerja);
            renderStoKecamatan(summary.sto.by_kecamatan);
        })
        .fail(function (xhr)
        {
            showLoadError();
            showMessage(readErrorMessage(xhr), 'error');
        });
});
