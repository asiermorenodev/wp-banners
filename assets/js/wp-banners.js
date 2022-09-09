class rrBanner {
    constructor() {
        this.script = document.getElementById('wp-banners-script');
        this.siteId = this.script.getAttribute('data-site-id');
        this.version = '1';

        /*********************************************************/
        /*** TODO: This is only for testing. Remove when done. ***/

        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);

        if (urlParams.has('site-id')) {
            this.siteId = urlParams.get('site-id');
        }

        if (urlParams.has('version')) {
            let version = urlParams.get('version');

            if ( version === '1' || version === '2' || version === '3' ) {
                this.version = urlParams.get('version');
            }
        }

        /*********************************************************/

        let request    = new XMLHttpRequest(),
            endpoint   = 'https://recyclingrules.org/wp-json/rr/v1/banner/',
            requestURL = endpoint + '?site-id=' + this.siteId + '&version=' + this.version + '&t=' + Date.now();

        request.open('GET', requestURL);
        request.send();
        request.onload = () => {
            if (request.status === 200) {
                console.log(request.response);
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