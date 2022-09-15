class rrBanner {
    constructor() {
        this.script = document.getElementById('wp-banners-script');
        this.id     = this.script.getAttribute('data-id');
        this.siteId = this.script.getAttribute('data-site-id');

        let url        = new URL(document.currentScript.src).origin,
            request    = new XMLHttpRequest(),
            endpoint   = url + '/wp-json/rr/v1/wp-banner/',
            requestURL = endpoint + '?id=' + this.id;

        if ( this.siteId !== null ) {
            requestURL += 'site-id=' + this.siteId;
        }

        request.open('GET', requestURL);
        request.send();
        request.onload = () => {
            if (request.status === 200) {
                this.data = JSON.parse(request.response);
                this.init();
            } else {
                console.log(`Error ${response.status} ${response.statusText}`);
            }
        }
    }

    init() {
        this.loadAssets();
        this.render();
    }

    loadAssets() {
        if (typeof this.data !== 'undefined' && typeof this.data.assets !== 'undefined') {
            for (let i=0; i < this.data.assets.length; i++) {
                let link = document.createElement('link');
                for (let key in this.data.assets[i]) {
                    link[key] = this.data.assets[i][key];
                }
                document.head.appendChild(link);
            }
        }

    }

    render() {
        const bannerContainer = document.createElement('div');
        bannerContainer.id = 'rr-banner-container';
        bannerContainer.className = 'v' + this.version;
        bannerContainer.innerHTML = this.data.html;
        this.script.parentNode.insertBefore(bannerContainer, this.script.nextSibling);
    }
}

new rrBanner();