import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.heroCarousel = function () {
    return {
        index: 0,
        count: 0,
        timer: null,
        init() {
            this.count = this.$el.querySelectorAll('[data-slide]').length;
            this.play();
        },
        play() {
            this.stop();
            if (this.count < 2) {
                return;
            }
            this.timer = setInterval(() => {
                this.index = (this.index + 1) % this.count;
            }, 7000);
        },
        stop() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        go(position) {
            this.index = position;
            this.play();
        },
        next() {
            this.index = (this.index + 1) % this.count;
            this.play();
        },
        prev() {
            this.index = (this.index - 1 + this.count) % this.count;
            this.play();
        },
    };
};

Alpine.start();
