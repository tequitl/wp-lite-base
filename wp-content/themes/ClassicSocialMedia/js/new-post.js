(function($) {
  $(function() {
    var $form = $('.csm-new-post-form');
    var $submit = $('#csm-np-submit');

    function showStatus($f, msg, isError) {
      var $status = $f.find('.csm-np-status');
      $status.text(msg).toggleClass('error', !!isError);
    }

    $submit.on('click', function(e) {
      e.preventDefault();
      var $f = $(this).closest('form');

      var data = {
        action: 'csm_new_post',
        csm_new_post_nonce: $f.find('input[name="csm_new_post_nonce"]').val(),
        csm_post_title: $f.find('[name="csm_post_title"]').val(),
        csm_post_content: $f.find('[name="csm_post_content"]').val()
      };

      // Basic validation
      if (!data.csm_post_title || !data.csm_post_content) {
        showStatus($f, 'Please provide both title and content.', true);
        return;
      }

      $.ajax({
        url: (window.CSMNewPost && CSMNewPost.ajax_url) ? CSMNewPost.ajax_url : '/wp-admin/admin-ajax.php',
        method: 'POST',
        data: data,
        beforeSend: function() {
          $submit.prop('disabled', true);
          showStatus($f, 'Posting...');
        },
        success: function(resp) {
          if (resp && resp.success) {
            showStatus($f, 'Posted!');

            // Reset fields
            $f[0].reset();

            // Optionally prepend the new post to the list (basic example)
            // If you want to render a full card, you could fetch markup via a custom template or REST.
            // Here we just prompt the user with a link.
            if (resp.data && resp.data.permalink) {
                //reload the page
                location.reload();
              //showStatus($f, 'Posted! View: ' + resp.data.permalink);
            }
          } else {
            var msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Failed to post';
            showStatus($f, msg, true);
          }
        },
        error: function() {
          showStatus($f, 'Network error', true);
        },
        complete: function() {
          $submit.prop('disabled', false);
        }
      });
    });
  });
})(jQuery);