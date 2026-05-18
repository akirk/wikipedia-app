<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
</div>
</main>
<script>
(function () {
    function languageDisplayName(language) {
        var name = language.localname || language.label || language.name || language.code || '';
        var autonym = language.name || language.autonym || '';
        var label = autonym && autonym !== name ? name + ' - ' + autonym : name;
        return label + ' (' + language.code + ')';
    }

    function createLanguageMoveButton(list, direction) {
        var button = document.createElement('button');
        var text = list.getAttribute('data-move-' + direction + '-text') || ('up' === direction ? 'Move up' : 'Move down');
        button.className = 'wiki-btn secondary wiki-icon-btn';
        button.type = 'button';
        button.setAttribute('data-wiki-language-move', direction);
        button.setAttribute('aria-label', text);
        button.title = text;
        button.innerHTML = 'up' === direction ? '&uarr;' : '&darr;';
        return button;
    }

    function createLanguageItem(list, language) {
        var item = document.createElement('li');
        var hidden = document.createElement('input');
        var name = document.createElement('span');
        var remove = document.createElement('button');
        var removeText = list.getAttribute('data-remove-text') || 'Remove';

        item.setAttribute('data-wiki-language-item', '');
        item.setAttribute('data-language-code', language.code);

        hidden.type = 'hidden';
        hidden.name = 'languages[]';
        hidden.value = language.code;
        item.appendChild(hidden);

        name.className = 'wiki-language-name';
        name.textContent = languageDisplayName(language);
        item.appendChild(name);

        item.appendChild(createLanguageMoveButton(list, 'up'));
        item.appendChild(createLanguageMoveButton(list, 'down'));

        remove.className = 'wiki-btn secondary wiki-icon-btn';
        remove.type = 'button';
        remove.setAttribute('data-wiki-language-remove', '');
        remove.setAttribute('aria-label', removeText + ' ' + languageDisplayName(language));
        remove.title = removeText;
        remove.innerHTML = '&times;';
        item.appendChild(remove);

        return item;
    }

    function refreshLanguageButtons(list) {
        var items = list.querySelectorAll('[data-wiki-language-item]');
        items.forEach(function (item, index) {
            var up = item.querySelector('[data-wiki-language-move="up"]');
            var down = item.querySelector('[data-wiki-language-move="down"]');
            if (up) {
                up.disabled = 0 === index;
            }
            if (down) {
                down.disabled = index === items.length - 1;
            }
        });
    }

    document.querySelectorAll('[data-wiki-language-order]').forEach(function (list) {
        list.addEventListener('click', function (event) {
            var move = event.target.closest ? event.target.closest('[data-wiki-language-move]') : null;
            var remove = event.target.closest ? event.target.closest('[data-wiki-language-remove]') : null;
            var button = move || remove;
            var item = button ? button.closest('[data-wiki-language-item]') : null;
            var direction = move ? move.getAttribute('data-wiki-language-move') : '';

            if (!button || !item || !list.contains(item)) {
                return;
            }

            if (remove) {
                item.remove();
                refreshLanguageButtons(list);
                return;
            }

            if ('up' === direction && item.previousElementSibling) {
                list.insertBefore(item, item.previousElementSibling);
            } else if ('down' === direction && item.nextElementSibling) {
                list.insertBefore(item.nextElementSibling, item);
            }

            refreshLanguageButtons(list);
            button.focus();
        });

        refreshLanguageButtons(list);
    });

    document.querySelectorAll('[data-wiki-language-picker]').forEach(function (picker) {
        var input = picker.querySelector('[data-wiki-language-search]');
        var results = picker.querySelector('[data-wiki-language-results]');
        var list = document.getElementById(picker.getAttribute('data-language-list') || '');
        var languages = null;
        var loading = false;

        if (!input || !results || !list || !window.fetch || !window.URLSearchParams) {
            return;
        }

        function selectedCodes() {
            return Array.prototype.map.call(list.querySelectorAll('[data-language-code]'), function (item) {
                return item.getAttribute('data-language-code');
            });
        }

        function renderLanguageResults(query) {
            var lowerQuery = query.toLowerCase();
            var selected = selectedCodes();
            var matches = (languages || []).filter(function (language) {
                var text = (language.code + ' ' + (language.localname || '') + ' ' + (language.name || '')).toLowerCase();
                return selected.indexOf(language.code) === -1 && text.indexOf(lowerQuery) !== -1;
            }).slice(0, 8);

            results.textContent = '';
            if (!query) {
                results.hidden = true;
                return;
            }

            if (!matches.length) {
                var empty = document.createElement('div');
                empty.className = 'wiki-language-result';
                empty.textContent = picker.getAttribute('data-empty-text') || 'No languages found.';
                results.appendChild(empty);
                results.hidden = false;
                return;
            }

            matches.forEach(function (language) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'wiki-language-result';
                button.textContent = languageDisplayName(language);
                button.addEventListener('click', function () {
                    list.appendChild(createLanguageItem(list, language));
                    refreshLanguageButtons(list);
                    input.value = '';
                    results.hidden = true;
                    input.focus();
                });
                results.appendChild(button);
            });
            results.hidden = false;
        }

        function loadLanguages(callback) {
            if (languages) {
                callback();
                return;
            }

            if (loading) {
                return;
            }

            loading = true;
            results.textContent = picker.getAttribute('data-loading-text') || 'Loading languages...';
            results.hidden = false;

            var params = new URLSearchParams({
                action: 'sitematrix',
                smtype: 'language|special',
                smlangprop: 'code|name|localname|site',
                smsiteprop: 'url|dbname|code|sitename',
                formatversion: '2',
                format: 'json',
                origin: '*'
            });

            fetch('https://en.wikipedia.org/w/api.php?' + params.toString(), { credentials: 'omit' })
                .then(function (response) {
                    return response.ok ? response.json() : null;
                })
                .then(function (data) {
                    languages = [];
                    if (data && data.sitematrix) {
                        Object.keys(data.sitematrix).forEach(function (key) {
                            var language = data.sitematrix[key];
                            if ('specials' === key && Array.isArray(language)) {
                                language.forEach(function (site) {
                                    if (site && 'simple' === site.code && site.dbname === 'simplewiki' && site.url === 'https://simple.wikipedia.org') {
                                        languages.push({
                                            code: 'simple',
                                            localname: 'Simple English',
                                            name: 'Simple English'
                                        });
                                    }
                                });
                                return;
                            }

                            var sites = language && Array.isArray(language.site) ? language.site : [];
                            var code = language && language.code ? language.code : '';
                            var hasWikipedia = sites.some(function (site) {
                                if (!site) {
                                    return false;
                                }

                                var isWikipediaProject = 'wiki' === site.code || site.dbname === code + 'wiki';
                                return isWikipediaProject && !('closed' in site) && !('private' in site) && !('fishbowl' in site) && site.url === 'https://' + code + '.wikipedia.org';
                            });

                            if (hasWikipedia) {
                                languages.push(language);
                            }
                        });
                    }
                    languages.sort(function (a, b) {
                        return languageDisplayName(a).localeCompare(languageDisplayName(b));
                    });
                    loading = false;
                    callback();
                })
                .catch(function () {
                    languages = [];
                    loading = false;
                    callback();
                });
        }

        input.addEventListener('input', function () {
            var query = input.value.trim();
            loadLanguages(function () {
                renderLanguageResults(query);
            });
        });
        input.addEventListener('focus', function () {
            if (input.value.trim()) {
                input.dispatchEvent(new Event('input'));
            }
        });
    });

    if (!window.fetch || !window.URLSearchParams) {
        return;
    }

    document.querySelectorAll('[data-wiki-quicksearch]').forEach(function (form) {
        var input = form.querySelector('[data-wiki-quicksearch-input]');
        var language = form.querySelector('input[name="language"]');
        var targetId = form.getAttribute('data-results-target') || '';
        var tabsId = form.getAttribute('data-language-tabs') || '';
        var target = targetId ? document.getElementById(targetId) : document.querySelector('[data-wiki-quicksearch-results]');
        var tabs = tabsId ? document.getElementById(tabsId) : document.querySelector('[data-wiki-language-tabs]');
        var articleBase = form.getAttribute('data-article-base') || '';
        var controller = null;
        var timer = null;

        if (!input || !language || !target || !articleBase) {
            return;
        }

        if (articleBase.slice(-1) !== '/') {
            articleBase += '/';
        }

        function dataText(name, fallback) {
            return form.getAttribute(name) || fallback;
        }

        function currentLanguage() {
            var value = (language.value || 'en').toLowerCase();
            return /^[a-z][a-z0-9-]{1,15}$/.test(value) ? value : 'en';
        }

        function currentLanguageLabel() {
            var active = tabs ? tabs.querySelector('[data-wiki-language].is-active') : null;
            var label = active ? active.textContent.trim() : '';
            return label || currentLanguage().toUpperCase();
        }

        function articleUrl(title) {
            return articleBase + encodeURIComponent(currentLanguage()) + '?title=' + encodeURIComponent(title);
        }

        function appendHidden(parent, name, value) {
            var field = document.createElement('input');
            field.type = 'hidden';
            field.name = name;
            field.value = value;
            parent.appendChild(field);
        }

        function plainText(html) {
            var element = document.createElement('div');
            element.innerHTML = String(html || '');
            return (element.textContent || element.innerText || '').replace(/\s+/g, ' ').trim();
        }

        function createNotice(message, className) {
            var notice = document.createElement('div');
            notice.className = 'wiki-notice' + (className ? ' ' + className : '');
            notice.textContent = message;
            return notice;
        }

        function setNotice(message, className) {
            target.textContent = '';
            target.appendChild(createNotice(message, className));
        }

        function updateAddress(query) {
            if (!window.history || !window.history.replaceState) {
                return;
            }

            var url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
            if (query) {
                url.searchParams.set('q', query);
                url.searchParams.set('language', currentLanguage());
            } else {
                url.searchParams.delete('q');
                url.searchParams.delete('language');
            }
            window.history.replaceState(null, '', url.toString());
        }

        function updateTabs(query) {
            if (!tabs) {
                return;
            }

            tabs.hidden = !query;
            tabs.querySelectorAll('[data-wiki-language]').forEach(function (link) {
                var code = link.getAttribute('data-wiki-language') || '';
                var url = new URL(form.getAttribute('action') || link.href, window.location.href);
                url.searchParams.set('q', query);
                url.searchParams.set('language', code);
                link.href = url.toString();
            });
        }

        function setActiveTab(code) {
            if (!tabs) {
                return;
            }

            tabs.querySelectorAll('[data-wiki-language]').forEach(function (link) {
                var active = code === link.getAttribute('data-wiki-language');
                link.classList.toggle('is-active', active);
                if (active) {
                    link.setAttribute('aria-current', 'true');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        }

        function renderResults(query, items) {
            var list = document.createElement('ul');

            target.textContent = '';

            if (!items.length) {
                target.appendChild(createNotice(dataText('data-no-results-text', 'No Wikipedia results found.')));
                return;
            }

            list.className = 'wiki-results';
            items.forEach(function (item) {
                var title = item && item.title ? item.title : '';
                var pageId = item && item.pageid ? String(item.pageid) : '';
                var snippet = item && item.snippet ? plainText(item.snippet) : '';
                var wordCount = item && item.wordcount ? Number(item.wordcount) : 0;
                var result = document.createElement('li');
                var titleHeading = document.createElement('h2');
                var titleLink = document.createElement('a');
                var meta = document.createElement('div');
                var languageMeta = document.createElement('span');
                var tools = document.createElement('div');
                var read = document.createElement('a');

                if (!title || !pageId) {
                    return;
                }

                result.className = 'wiki-result';
                titleLink.href = articleUrl(title);
                titleLink.textContent = title;
                titleHeading.appendChild(titleLink);
                result.appendChild(titleHeading);

                meta.className = 'wiki-meta';
                languageMeta.textContent = currentLanguageLabel() + ' (' + currentLanguage() + ')';
                meta.appendChild(languageMeta);
                if (wordCount) {
                    var words = document.createElement('span');
                    words.textContent = wordCount.toLocaleString() + ' ' + dataText('data-words-text', 'words');
                    meta.appendChild(words);
                }
                result.appendChild(meta);

                if (snippet) {
                    var paragraph = document.createElement('p');
                    paragraph.textContent = snippet;
                    result.appendChild(paragraph);
                }

                tools.className = 'wiki-article-tools';
                read.className = 'wiki-btn secondary';
                read.href = articleUrl(title);
                read.textContent = dataText('data-read-text', 'Read');
                tools.appendChild(read);

                if ('1' === form.getAttribute('data-can-save')) {
                    var save = document.createElement('form');
                    var button = document.createElement('button');
                    save.className = 'wiki-inline-form';
                    save.method = 'post';
                    save.action = form.getAttribute('data-save-action') || '';
                    appendHidden(save, 'action', 'wikipedia_save_article');
                    appendHidden(save, '_wpnonce', form.getAttribute('data-save-nonce') || '');
                    appendHidden(save, 'page_id', pageId);
                    appendHidden(save, 'title', title);
                    appendHidden(save, 'language', currentLanguage());
                    button.className = 'wiki-btn secondary';
                    button.type = 'submit';
                    button.textContent = dataText('data-save-text', 'Save article');
                    save.appendChild(button);
                    tools.appendChild(save);
                }

                result.appendChild(tools);
                list.appendChild(result);
            });

            if (!list.children.length) {
                target.appendChild(createNotice(dataText('data-no-results-text', 'No Wikipedia results found.')));
                return;
            }

            target.appendChild(list);
        }

        function fetchResults() {
            var query = input.value.trim();
            var requestLanguage = currentLanguage();
            updateTabs(query);

            if (!query) {
                if (controller) {
                    controller.abort();
                }
                target.textContent = '';
                updateAddress('');
                return;
            }

            if (controller) {
                controller.abort();
            }
            controller = new AbortController();
            setNotice(dataText('data-searching-text', 'Searching Wikipedia...'));

            var params = new URLSearchParams({
                action: 'query',
                list: 'search',
                srsearch: query,
                srlimit: '12',
                srprop: 'snippet|wordcount|timestamp|size',
                formatversion: '2',
                format: 'json',
                utf8: '1',
                origin: '*'
            });

            fetch('https://' + requestLanguage + '.wikipedia.org/w/api.php?' + params.toString(), {
                signal: controller.signal,
                credentials: 'omit'
            })
                .then(function (response) {
                    return response.ok ? response.json() : null;
                })
                .then(function (data) {
                    var pages = data && data.query && Array.isArray(data.query.search) ? data.query.search : [];
                    if (query !== input.value.trim() || requestLanguage !== currentLanguage()) {
                        return;
                    }
                    renderResults(query, pages);
                    updateAddress(query);
                })
                .catch(function (error) {
                    if (!error || error.name !== 'AbortError') {
                        setNotice(dataText('data-no-results-text', 'No Wikipedia results found.'), 'error');
                    }
                });
        }

        function queueFetch(delay) {
            window.clearTimeout(timer);
            timer = window.setTimeout(fetchResults, 'number' === typeof delay ? delay : 180);
        }

        input.addEventListener('input', function () {
            updateTabs(input.value.trim());
            queueFetch();
        });

        if (tabs) {
            tabs.addEventListener('click', function (event) {
                var link = event.target.closest ? event.target.closest('[data-wiki-language]') : null;
                var code = link ? link.getAttribute('data-wiki-language') : '';
                if (!link || !tabs.contains(link) || !code || !input.value.trim()) {
                    return;
                }

                event.preventDefault();
                language.value = code;
                setActiveTab(code);
                updateTabs(input.value.trim());
                queueFetch(0);
                input.focus();
            });
        }

        updateTabs(input.value.trim());
        setActiveTab(currentLanguage());
    });
})();
</script>
<?php wp_app_body_close(); ?>
</body>
</html>
