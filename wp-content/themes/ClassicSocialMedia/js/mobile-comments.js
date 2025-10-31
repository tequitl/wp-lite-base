jQuery(function ($) {
  $(document).on('click', '.csm-comments-toggle', function (e) {
    e.preventDefault();

    var $btn = $(this);
    var postId = parseInt($btn.data('post-id'), 10);
    if (!postId) return;

    var $container = $('#csm-comments-' + postId);

    // Toggle if already loaded
    if ($container.data('loaded')) {
      $container.slideToggle(150).toggleClass('is-open');
      return;
    }

    // First load: show loading state
    $btn.prop('disabled', true).addClass('is-loading');
    $container
      .addClass('csm-comments-container')
      .html('<div class="csm-comments-loading">Loading…</div>')
      .slideDown(150);

    $.post(CSMComments.ajax_url, {
      action: 'csm_load_comments',
      post_id: postId,
      nonce: CSMComments.nonce
    })
      .done(function (resp) {
        if (resp && resp.success && resp.data && resp.data.html) {
          $container.html(resp.data.html);
          $container.data('loaded', true).addClass('is-open');
        } else {
          $container.html('<div class="csm-comments-error">Failed to load comments.</div>');
        }
      })
      .fail(function () {
        $container.html('<div class="csm-comments-error">Network error.</div>');
      })
      .always(function () {
        $btn.prop('disabled', false).removeClass('is-loading');
      });
  });
});

// Delegated handlers so they work on AJAX-inserted forms
jQuery(function ($) {
    // Envío AJAX al hacer clic en el botón (type="button")
    $(document).on('click', '.csm-ajax-submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn  = $(this);
        var $form = $btn.closest('form');
        var $container = $form.closest('.csm-comments-container');

        // Detectar postId de forma robusta
        var postId =
            parseInt($form.find('input[name="comment_post_ID"]').val(), 10) ||
            parseInt($btn.data('post-id'), 10) ||
            parseInt(($form.closest('[data-post-id]').data('post-id')), 10) || 0;

        var commentText = ($form.find('textarea[name="comment"]').val() || '').trim();
        var data = {
            action: 'csm_post_comment_ajax',
            post_id: postId,
            comment_post_ID: postId,
            comment: commentText,
            comment_parent: $form.find('input[name="comment_parent"]').val() || 0,
            nonce: $form.find('input[name="nonce"]').val() || ''
        };

        console.log(data);

        // Campos opcionales para invitados
        var author = $form.find('input[name="author"]').val();
        var email  = $form.find('input[name="email"]').val();
        var url    = $form.find('input[name="url"]').val();
        if (typeof author !== 'undefined') data.author = author;
        if (typeof email !== 'undefined') data.email = email;
        if (typeof url !== 'undefined') data.url = url;

        // Validaciones básicas
        if (!data.post_id) {
            showMessage($container, 'ID de publicación inválido.', 'error');
            return;
        }
        if (!data.comment) {
            showMessage($container, 'Por favor, escribe el texto de tu comentario.', 'error');
            return;
        }

        setLoading($container, true);

        $.post(csmComments && csmComments.ajax_url ? csmComments.ajax_url : (window.ajaxurl || '/wp-admin/admin-ajax.php'), data)
            .done(function (resp) {
                if (resp && resp.success && resp.data && resp.data.html) {
                    var $list = $container.find('.csm-comments-list');
                    if (!$list.length) $list = $('<div class="csm-comments-list"></div>').appendTo($container);

                    $list.append(resp.data.html);
                    $form.find('textarea[name="comment"]').val('');
                    showMessage($container, 'Comentario publicado.', 'ok');
                } else {
                    var err = (resp && resp.data && resp.data.message) ? resp.data.message : 'No se pudo publicar el comentario.';
                    console.log(resp);
                    showMessage($container, err, 'error');
                }
            })
            .fail(function () {
                showMessage($container, 'Error de red. Intenta nuevamente.', 'error');
            })
            .always(function () {
                setLoading($container, false);
            });
    });

    function setLoading($container, on) {
        var $msg = $container.find('.csm-comments-loading');
        if (!$msg.length) {
            $msg = $('<div class="csm-comments-loading">Cargando…</div>').appendTo($container);
        }
        $msg.toggle(!!on);
    }

    function showMessage($container, text, type) {
        var $box = $container.find('.csm-comments-message');
        if (!$box.length) {
            $box = $('<div class="csm-comments-message"></div>').appendTo($container);
        }
        $box.text(text).attr('data-type', type).show();
        setTimeout(function () { $box.fadeOut(300); }, 3000);
    }
});