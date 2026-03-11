$(document).ready(function () {

  // Afficher champ upload selon type de post
  $('#postType').change(function () {
    $('#mediaUploadField').toggle($(this).val() === 'photo' || $(this).val() === 'video');
  });

  // Like
  $(document).on('click', '.btn-like', function () {
    var btn = $(this), postId = btn.data('id');
    $.post('ajax/like_post.php', { post_id: postId }, function (res) {
      if (res.success) btn.find('.like-count').text(res.totalLikes);
    }, 'json');
  });

  // Toggle commentaires
  $(document).on('click', '.btn-toggle-comments', function () {
    $('#comments-' + $(this).data('id')).toggleClass('d-none');
  });

  // Envoyer commentaire
  $(document).on('click', '.btn-send-comment', function () {
    var postId = $(this).data('id');
    var input  = $('.comment-input[data-id="' + postId + '"]');
    var content = input.val().trim();
    if (!content) return;
    $.post('ajax/add_comment.php', { post_id: postId, content: content }, function (res) {
      if (res.success) {
        input.val('');
        var html = '<div class="d-flex gap-2 mb-1 align-items-start" id="comment-' + res.comment_id + '">'
          + '<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:.8rem;flex-shrink:0;">' + res.initial + '</div>'
          + '<div class="bg-light rounded p-2 flex-grow-1 small"><strong>' + res.name + '</strong> ' + res.content + '</div>'
          + '<button class="btn btn-sm text-danger p-0 btn-delete-comment" data-id="' + res.comment_id + '">✕</button>'
          + '</div>';
        $('#comments-' + postId + ' .comments-list').append(html);
      }
    }, 'json');
  });

  // Supprimer commentaire
  $(document).on('click', '.btn-delete-comment', function () {
    var id = $(this).data('id');
    $.post('ajax/delete_comment.php', { comment_id: id }, function (res) {
      if (res.success) $('#comment-' + id).remove();
    }, 'json');
  });

  // Repost — afficher/cacher zone
  $(document).on('click', '.btn-repost', function () {
    $('#repost-zone-' + $(this).data('id')).toggleClass('d-none');
  });
  $(document).on('click', '.btn-cancel-repost', function () {
    $('#repost-zone-' + $(this).data('id')).addClass('d-none');
  });
  $(document).on('click', '.btn-confirm-repost', function () {
    var id      = $(this).data('id');
    var comment = $('#repost-zone-' + id + ' .repost-comment').val().trim();
    $.post('ajax/repost.php', { post_id: id, comment: comment }, function (res) {
      if (res.success) { location.reload(); }
      else { alert('Erreur lors du repost.'); }
    }, 'json');
  });

  // Modifier post
  $(document).on('click', '.btn-edit-post', function () {
    var id = $(this).data('id');
    $('#content-text-' + id).addClass('d-none');
    $('#edit-zone-' + id).removeClass('d-none');
  });
  $(document).on('click', '.btn-cancel-edit', function () {
    var id = $(this).data('id');
    $('#edit-zone-' + id).addClass('d-none');
    $('#content-text-' + id).removeClass('d-none');
  });
  $(document).on('click', '.btn-save-edit', function () {
    var id      = $(this).data('id');
    var content = $('#edit-zone-' + id + ' .edit-textarea').val().trim();
    if (!content) return;
    $.post('ajax/edit_post.php', { post_id: id, content: content }, function (res) {
      if (res.success) {
        $('#content-text-' + id).html(res.content.replace(/\n/g, '<br>')).removeClass('d-none');
        $('#edit-zone-' + id).addClass('d-none');
      }
    }, 'json');
  });

});
