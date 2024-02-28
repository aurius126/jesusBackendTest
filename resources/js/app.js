import './bootstrap';
import Echo from "laravel-echo"

console.log('first')

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '51e653493e6c48205227',
    cluster: 'us2',
    encrypted: true
});

window.Echo.channel('counter')
    .listen('.CounterUpdated', (e) => {
        console.log(e)
        document.getElementById('contador').innerText = e.count;
    });