var Discussion = function (options) {
    !options && $.error('options required');
    this.currentUser = options.currentUser;
    moment.locale('fr');
};

Discussion.prototype.bindEvents = function () {
    var self = this;

    self.loadActiveUsers();

    setInterval(function () {
        self.loadActiveUsers();
        self.loadActiveChat();
    }, 3000);

    $(document).on('click', '.usernameLink', function (event) {
        event.preventDefault();

        $('.chat_list').removeClass('active_chat');
        $(this).closest('.chat_list').addClass('active_chat');

        self.loadActiveChat();
    });

    $('form.message_box_write').on('submit', function (event) {
        event.preventDefault();

        var receiver = $('.active_chat').first().attr('data-otherUser');
        var $inputContent = $('input[name="content"]');

        if ($inputContent.val().trim() !== "") {
            axios({
                method: 'post',
                url: '/discussion/store/' + receiver,
                responseType: 'json',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                data: $(this).serialize()
            }).then(function () {
                $('.msg_history').append('<div class="outgoing_msg">\n' +
                    '                    <div class="sent_msg">\n' +
                    '                        <p>' + $inputContent.val() + '</p>\n' +
                    '                        <span class="time_date"> ' + moment().format('LLL') + '</span></div>\n' +
                    '                </div>');
                $inputContent.val('');
            });
        }
    });

    $('form.message_box_write input.write_msg').on('focus', function (event) {
        event.preventDefault();

        self.markDiscussionAsRead();
    });
};

Discussion.prototype.loadActiveUsers = function () {
    axios({
        method: 'get',
        url: '/users',
        headers: { 'content-type': 'application/json' },
        responseType: 'json',
    }).then(function ({ data }) {
        var selectedUser = $('.active_chat').first().attr('data-otherUser');

        $('.inbox_chat').html('');

        if(undefined === selectedUser && data.length > 0) {
            selectedUser = data[0].id;
        }

        data.forEach(({ id, firstname, lastname, isOnline, image, countMessages = 0 }) => {
            $('.inbox_chat').append('<div class="chat_list ' + (id == selectedUser ? 'active_chat' : '') + '" data-otherUser="' + id + '">\n' +
                '                    <div class="chat_people">\n' +
                '                        <div class="chat_img"><img src="https://ptetutorials.com/images/user-profile.png" alt="sunil"></div>\n' +
                '                        <a href="#" class="card-link chat_ib usernameLink">\n' +
                '                            <h5>' + firstname + ' ' + lastname + '</h5>\n' +
                '                            <svg height="10" width="10" class="online-status"><circle cx="5" cy="5" r="5" fill="' + (true === isOnline ? 'green' : 'red') + '" /></svg>' +
                '                            <span class="badge badge-info">'+ (0 !== countMessages ? countMessages : '') +'</span>' +
                '                        </a>\n' +
                '                    </div>\n' +
                '                </div>');
        });
    });
};

Discussion.prototype.loadActiveChat = function () {
    var otherUser = $('.active_chat').first().attr('data-otherUser');
    var currentUserID = this.currentUser;

    axios({
        method: 'get',
        url: '/discussion/with/' + otherUser,
        responseType: 'json',
    }).then(function ({ data }) {
        $('.msg_history').html('');
        data.forEach(({ sender, content, createdAt }) => {
            if (sender.id != currentUserID) {
                $('.msg_history').append('<div class="incoming_msg">\n' +
                    '                    <div class="incoming_msg_img"><img src="https://ptetutorials.com/images/user-profile.png" alt="sunil"></div>\n' +
                    '                    <div class="received_msg">\n' +
                    '                        <div class="received_withd_msg">\n' +
                    '                            <p>' + content + '</p>\n' +
                    '                            <span class="time_date"> ' + moment(createdAt).format('LLL') + '</span></div>\n' +
                    '                    </div>\n' +
                    '                </div>');
            } else {
                $('.msg_history').append('<div class="outgoing_msg">\n' +
                    '                    <div class="sent_msg">\n' +
                    '                        <p>' + content + '</p>\n' +
                    '                        <span class="time_date"> ' + moment(createdAt).format('LLL') + '</span></div>\n' +
                    '                </div>');
            }
        });
    });
};

Discussion.prototype.markDiscussionAsRead = function () {
    var otherUser = $('.active_chat').first().attr('data-otherUser');

    axios({
        method: 'post',
        url: '/discussion/mark-as-read/' + otherUser,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
    }).then(function () {
        $('.active_chat').first().find('.not-read-count').html('');
    });
}
