function showFlashAlert()
{
    var flashAlert = document.getElementById('flash-alert');
    var messages = JSON.parse(flashAlert.dataset.messages);
    var messageList = document.createElement('div');
    var pageStyle = getComputedStyle(document.documentElement);

    messages.forEach(function (message)
    {
        var messageLine = document.createElement('div');

        messageLine.textContent = message;
        messageList.appendChild(messageLine);
    });

    Swal.fire(
    {
        icon: flashAlert.dataset.type,
        title: flashAlert.dataset.title,
        html: messageList,
        confirmButtonText: 'OK',
        confirmButtonColor: pageStyle.getPropertyValue('--hcm-brand').trim() || pageStyle.getPropertyValue('--bs-primary').trim()
    });
}

showFlashAlert();
