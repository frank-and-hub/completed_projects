

var fileInput = document.querySelector('.account-file-input'),
    resetFileInput = document.querySelector('.account-image-reset');
var accountUserImage = document.getElementById('uploadedAvatar');
var resetImage;
document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
        const deactivateAcc = document.querySelector('#formAccountDeactivation');

        // Update/reset user image of account page


        if (accountUserImage) {
            resetImage = accountUserImage.src;
            fileInput.onchange = () => {
                if (fileInput.files[0]) {
                    accountUserImage.src = window.URL.createObjectURL(fileInput.files[0]);
                }
            };
            // resetFileInput.onclick = () => {
            //     fileInput.value = '';
            //     accountUserImage.src = resetImage;

            // };

        }
    })();


});
function rst(e) {
    var url = $(e).attr('link');
    var id = $(e).attr('id');
    fileInput.value = '';
    accountUserImage.src = resetImage;
    var default_img = $(e).attr('default-img-url');

    if (id != '') {
        deleteImage(url, default_img);
    }


}

function deleteImage(uRL, default_img) {
    $.ajax({
        url: uRL,
        success: function (res) {
            console.info(res.status);
            accountUserImage.src = default_img;

        }
    })
}

