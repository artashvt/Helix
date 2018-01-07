$(document).ready(function () {
    // $(".element-to-truncate").dotdotdot();

    $('[data-toggle="tooltip"]').tooltip();

    $('#datetimepicker1').datetimepicker({autoclose: true});

    $('#edit-image').on('change', function () {
        var ext = $(this).val().split('.').pop().toLowerCase();
        if ($.inArray(ext, ['jpg', 'png', 'jpeg']) == -1) {
            alert('Invalid file!');
        } else {
            changelogo('#edit-image', '#edit-image-label');
        }
    });
    $(".btn-del-article").on("click", deleteArticle);
});

function changelogo(file, img) {
    var inp = $(file)[0];
    var file = inp.files[0];
    var reader = new FileReader();
    reader.onload = function () {
        $(img).css('background-image', 'url(' + this.result + ')');
    };
    reader.readAsDataURL(file);
}

function deleteArticle(e) {
    e.preventDefault();
    var form = $(this).parent();
    var confirmText = "Are you sure you want to delete this Article?";
    if (confirm(confirmText)) {
        form.submit();
    }
}