;(function ($) {

    function isValidDate($wrap) {
        var year = $wrap.find('.aa').val();
        var month = parseInt($wrap.find('.mm').val()) - 1;
        var day = $wrap.find('.jj').val();
        var hour = $wrap.find('.hh').val();
        var min = $wrap.find('.mn').val();
        var attempt = new Date(year, month, day, hour, min);
        console.log(year, month, day, hour, min);

        return attempt.getFullYear() == year &&
               attempt.getMonth() == month &&
               attempt.getDate() == day &&
               attempt.getHours() == hour &&
               attempt.getMinutes() == min;
    }

    $(document).ready(function () {
        $('form#post').on('submit', function (e) {
            var $start = $('#event-publish-div .te-start-time');
            var $end = $('#event-publish-div .te-end-time');
            var invalid = false;

            if ($start.length && !isValidDate($start)) {
                invalid = true;
                $start.addClass('form-invalid');
            } else {
                $start.removeClass('form-invalid');
            }

            if ($end.length && !isValidDate($end)) {
                invalid = true;
                $end.addClass('form-invalid');
            } else {
                $end.removeClass('form-invalid');
            }

            if (invalid) {
                e.preventDefault();
                return false;
            }
        });
    });

}(jQuery));
