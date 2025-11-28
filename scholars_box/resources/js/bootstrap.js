import 'bootstrap';

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window._ = import('lodash');
try {
    window.Popper = import('popper.js').default;
    window.$ = window.jQuery = import('jquery');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name=asd]').attr('content')
        }
    });

} catch (e) {}
