//getEmailInboxUnreadCount();
function getEmailInboxUnreadCount() {
    $.ajax({
        url: admin_url + 'webmails/getInboxUnreadCount',
        method: "POST",
        dataType: 'json',
        async: true
    }).done(function (result) {
        if (result.success) {
            $("#menu .menu-item-emails span.menu-text").find('.menu-badge').remove();
            if (result.inboxCount > 0) {
                $("#menu .menu-item-emails span.menu-text").append('<span class="badge menu-badge bg-info">' + result.inboxCount + '</span>');
                var inboxBadge = $('.list-group-item[data-name="INBOX"]').find('.badge');
                inboxBadge.removeClass('hide');
                inboxBadge.html("<b>" + result.inboxCount + "</b>")
            } else {
                $('.list-group-item[data-name="INBOX"]').find('.badge').html("<b>" + result.inboxCount + "</b>");
                $('.list-group-item[data-name="INBOX"]').find('.badge').addClass('hide');
            }
        }
    });
}