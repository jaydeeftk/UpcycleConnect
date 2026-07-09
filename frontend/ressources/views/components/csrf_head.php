<meta name="csrf-token" content="<?= htmlspecialchars(function_exists('csrf_token') ? csrf_token() : '', ENT_QUOTES) ?>">
<script>
(function () {
    var m = document.querySelector('meta[name="csrf-token"]');
    var TOKEN = m ? m.getAttribute('content') : '';
    function mutating(method) { method = (method || 'GET').toUpperCase(); return method !== 'GET' && method !== 'HEAD' && method !== 'OPTIONS'; }
    function sameOrigin(url) { try { if (!url) return true; if (url.indexOf('http') !== 0 && url.indexOf('//') !== 0) return true; return url.indexOf(location.origin) === 0; } catch (e) { return false; } }
    if (window.fetch) {
        var of = window.fetch;
        window.fetch = function (input, init) {
            init = init || {};
            var url = (typeof input === 'string') ? input : (input && input.url) || '';
            var method = init.method || (typeof input === 'object' && input && input.method) || 'GET';
            if (mutating(method) && sameOrigin(url)) {
                var h = new Headers(init.headers || (typeof input === 'object' && input && input.headers) || {});
                if (!h.has('X-CSRF-Token')) h.set('X-CSRF-Token', TOKEN);
                init.headers = h;
            }
            return of.call(this, input, init);
        };
    }
    var oo = XMLHttpRequest.prototype.open, osnd = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url) { this.__csrfM = method; this.__csrfU = url; return oo.apply(this, arguments); };
    XMLHttpRequest.prototype.send = function (body) { try { if (mutating(this.__csrfM) && sameOrigin(this.__csrfU)) this.setRequestHeader('X-CSRF-Token', TOKEN); } catch (e) { } return osnd.apply(this, arguments); };
})();
</script>
