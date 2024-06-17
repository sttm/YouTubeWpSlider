jQuery(document).ready(function($) {
    function copyToClipboard(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }

    // Обработчик клика по кнопке
    $(".shortcode-btn").on("click", function() {
        var shortcodeText = $("#shortcode-field").text().trim();
        copyToClipboard(shortcodeText);
        $(".shortcode-alert").fadeIn("slow").delay(1500).fadeOut("slow");
    });

    // Обработчик клика по кнопке "Delete"
  

    // Обработчик изменения состояния чекбокса
    $(document).on('change', '.video-checkbox', function() {
        // Ваш код для обработки изменения состояния чекбокса
        var videoId = $(this).data('video-id');
        var isChecked = $(this).prop('checked');

        $.ajax({
            type: 'POST',
            url: videoSliderAdminParams.ajaxurl,
            data: {
                action: 'update_video_list',
                video_id: videoId,
                is_checked: isChecked,
                security: videoSliderAdminParams.updateVideoListNonce,
            },
            success: function(response) {
                if (response.success) {
                    // alert(response.data); // You can replace this with a more user-friendly notification
                } else {
                    alert('Error updating video list.'); // You can replace this with a more user-friendly notification
                }
            },
            error: function(error) {
                alert('Error updating video list.');
                console.error(error);
            }
        });
    });
});
