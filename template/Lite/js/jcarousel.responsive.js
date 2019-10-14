(function($) {
    $(function() {
        var jcarousel = $('.jcarousel');

        jcarousel
            .on('jcarousel:reload jcarousel:create', function () {
                var width = jcarousel.innerWidth();

                if (width >= 750) {
                    width = width / 6;
                } else if (width >= 520) {
                    width = width / 5;
                } else if (width >= 440) {
                    width = width / 4;
                } else if (width >= 330) {
                    width = width / 3;
                } else if (width >= 290) {
                    width = width / 3;
                }
                width = width - 6;
                jcarousel.jcarousel('items').css('width', width + 'px');
            })
            .jcarousel({
                wrap: 'circular'
            });

        $('.jcarousel-prev')
            .jcarouselControl({
                target: '-=1'
            });

        $('.jcarousel-next')
            .jcarouselControl({
                target: '+=1'
            });
    });
})(jQuery);
