<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
</main>
<script>
(function () {
    var forms = document.querySelectorAll('[data-wiki-autocomplete]');
    if (!forms.length || !window.fetch || !window.URLSearchParams) {
        return;
    }

    forms.forEach(function (form) {
        var input = form.querySelector('[data-wiki-autocomplete-input]');
        var language = form.querySelector('select[name="language"]');
        var list = form.querySelector('.wiki-autocomplete');
        var articleBase = form.getAttribute('data-article-base') || '';
        var controller = null;
        var timer = null;
        var activeIndex = -1;
        var suggestions = [];

        if (!input || !language || !list || !articleBase) {
            return;
        }

        if (articleBase.slice(-1) !== '/') {
            articleBase += '/';
        }

        function currentLanguage() {
            var value = (language.value || 'en').toLowerCase();
            return /^[a-z][a-z0-9-]{1,15}$/.test(value) ? value : 'en';
        }

        function articleUrl(title) {
            return articleBase + encodeURIComponent(currentLanguage()) + '?title=' + encodeURIComponent(title);
        }

        function closeList() {
            list.hidden = true;
            list.textContent = '';
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
            suggestions = [];
        }

        function setActive(index) {
            var options = list.querySelectorAll('.wiki-autocomplete-option');
            options.forEach(function (option, i) {
                option.classList.toggle('is-active', i === index);
                option.setAttribute('aria-selected', i === index ? 'true' : 'false');
            });
            activeIndex = index;
        }

        function openArticle(title) {
            if (!title) {
                return;
            }
            window.location.href = articleUrl(title);
        }

        function render(items) {
            list.textContent = '';
            suggestions = items;
            activeIndex = -1;

            if (!items.length) {
                closeList();
                return;
            }

            items.forEach(function (item, index) {
                var option = document.createElement('button');
                option.type = 'button';
                option.className = 'wiki-autocomplete-option';
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                option.textContent = item;
                option.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                });
                option.addEventListener('click', function () {
                    openArticle(item);
                });
                option.addEventListener('mouseenter', function () {
                    setActive(index);
                });
                list.appendChild(option);
            });

            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function fetchSuggestions() {
            var query = input.value.trim();
            if (query.length < 2) {
                closeList();
                return;
            }

            if (controller) {
                controller.abort();
            }
            controller = new AbortController();

            var params = new URLSearchParams({
                action: 'query',
                list: 'prefixsearch',
                pssearch: query,
                psnamespace: '0',
                pslimit: '8',
                format: 'json',
                origin: '*'
            });

            fetch('https://' + currentLanguage() + '.wikipedia.org/w/api.php?' + params.toString(), {
                signal: controller.signal,
                credentials: 'omit'
            })
                .then(function (response) {
                    return response.ok ? response.json() : null;
                })
                .then(function (data) {
                    var pages = data && data.query && Array.isArray(data.query.prefixsearch) ? data.query.prefixsearch : [];
                    render(pages.map(function (page) {
                        return page.title || '';
                    }).filter(Boolean));
                })
                .catch(function (error) {
                    if (!error || error.name !== 'AbortError') {
                        closeList();
                    }
                });
        }

        function queueFetch() {
            window.clearTimeout(timer);
            timer = window.setTimeout(fetchSuggestions, 180);
        }

        input.addEventListener('input', queueFetch);
        input.addEventListener('focus', queueFetch);
        language.addEventListener('change', queueFetch);
        input.addEventListener('keydown', function (event) {
            if (list.hidden) {
                return;
            }

            if ('ArrowDown' === event.key) {
                event.preventDefault();
                setActive(Math.min(activeIndex + 1, suggestions.length - 1));
            } else if ('ArrowUp' === event.key) {
                event.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if ('Enter' === event.key && activeIndex >= 0) {
                event.preventDefault();
                openArticle(suggestions[activeIndex]);
            } else if ('Escape' === event.key) {
                closeList();
            }
        });

        document.addEventListener('click', function (event) {
            if (!form.contains(event.target)) {
                closeList();
            }
        });
    });
})();
</script>
<?php wp_app_body_close(); ?>
</body>
</html>
