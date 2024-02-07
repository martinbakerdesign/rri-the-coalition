class Slideshow {
    constructor (el) {
        this.el = el;
        this.imgs = [...el.querySelectorAll('img')]

        this.current = 0;
        this.max = this.imgs.length - 1;
        
        this.interval = null;
        this.duration = 5000;

        (this.max > 0) && this.init()
    }
    init = () => {
        this.interval = setInterval(this.onInterval, this.duration)
    }
    onInterval = () => {
        this.current = (this.current + 1) % this.imgs.length;

        this.updateDom();
    }
    updateDom = () => {
        this.el.dataset.current = this.current;

        this.imgs.forEach((img, i) => 
            this.toggleImg(img, i === this.current)
        )
    }
    toggleImg = (img, show) => {
        img.hidden = !show;
        img.setAttribute('aria-hidden', !show);
    }
}

export default Slideshow;