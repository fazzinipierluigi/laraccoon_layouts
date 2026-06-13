@php
    use Fazzinipierluigi\LaraccoonLayouts\Models\DatagridLayout;

    $strategy = config('raccoon_layouts.page_key_strategy', 'url');
    if ($strategy === 'route_name') {
        $rawKey = request()->route()?->getName() ?? request()->path();
    } else {
        $rawKey = request()->url();
    }
    $pageKey   = sha1($rawKey);
    $prefix    = '/' . trim(config('raccoon_layouts.route_prefix', 'raccoon-layouts'), '/');
    $phpLocale = config('raccoon_layouts.locale', 'en');

    $serverDefaultLayout = null;
    if (auth()->check()) {
        $def = DatagridLayout::where('user_id', auth()->id())
            ->where('page_key', $pageKey)
            ->where('is_default', true)
            ->first(['layout_data']);
        if ($def) {
            $serverDefaultLayout = $def->layout_data;
        }
    }
@endphp
<script>
(function () {
    var _config = window.RaccoonLayoutsConfig || {};
    var pageKey = _config.pageKey || '{{ $pageKey }}';
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    var routes = Object.assign({
        byPage:     '{{ $prefix }}/page/',
        store:      '{{ $prefix }}/store',
        update:     '{{ $prefix }}/',
        destroy:    '{{ $prefix }}/',
        setDefault: function(id) { return '{{ $prefix }}/' + id + '/default'; },
        copy:       function(id) { return '{{ $prefix }}/' + id + '/copy'; }
    }, _config.routes || {});

    var _i18n = {
        en: {
            placeholder:    '— Standard Layout —',
            menuBtn:        'Layout',
            load:           'Load',
            save:           'Save',
            rename:         'Rename',
            delete:         'Delete',
            copy:           'Copy',
            setDefault:     'Set as Default',
            badgePublic:    'Public',
            badgeDefault:   'Default',
            promptSave:     'Layout name:',
            promptRename:   'New name:',
            confirmDelete:  'Delete selected layout?',
            copyPrefix:     'Copy of ',
            errSave:        'Error saving: ',
            errRename:      'Error renaming: ',
            errDelete:      'Error deleting: ',
            errCopy:        'Error copying: ',
            errDefault:     'Error setting default: ',
            errLoad:        'Layout not found'
        },
        it: {
            placeholder:    '— Layout Standard —',
            menuBtn:        'Layout',
            load:           'Carica',
            save:           'Salva',
            rename:         'Rinomina',
            delete:         'Elimina',
            copy:           'Copia',
            setDefault:     'Imposta Default',
            badgePublic:    'Pubblico',
            badgeDefault:   'Default',
            promptSave:     'Nome del layout:',
            promptRename:   'Nuovo nome:',
            confirmDelete:  'Eliminare il layout selezionato?',
            copyPrefix:     'Copia di ',
            errSave:        'Errore durante il salvataggio: ',
            errRename:      'Errore durante la rinomina: ',
            errDelete:      'Errore durante l\'eliminazione: ',
            errCopy:        'Errore durante la copia: ',
            errDefault:     'Errore nell\'impostare il default: ',
            errLoad:        'Layout non trovato'
        },
        es: {
            placeholder:    '— Diseño Estándar —',
            menuBtn:        'Diseño',
            load:           'Cargar',
            save:           'Guardar',
            rename:         'Renombrar',
            delete:         'Eliminar',
            copy:           'Copiar',
            setDefault:     'Establecer por defecto',
            badgePublic:    'Público',
            badgeDefault:   'Por defecto',
            promptSave:     'Nombre del diseño:',
            promptRename:   'Nuevo nombre:',
            confirmDelete:  '¿Eliminar el diseño seleccionado?',
            copyPrefix:     'Copia de ',
            errSave:        'Error al guardar: ',
            errRename:      'Error al renombrar: ',
            errDelete:      'Error al eliminar: ',
            errCopy:        'Error al copiar: ',
            errDefault:     'Error al establecer por defecto: ',
            errLoad:        'Diseño no encontrado'
        },
        fr: {
            placeholder:    '— Mise en page standard —',
            menuBtn:        'Mise en page',
            load:           'Charger',
            save:           'Enregistrer',
            rename:         'Renommer',
            delete:         'Supprimer',
            copy:           'Copier',
            setDefault:     'Définir par défaut',
            badgePublic:    'Public',
            badgeDefault:   'Par défaut',
            promptSave:     'Nom de la mise en page :',
            promptRename:   'Nouveau nom :',
            confirmDelete:  'Supprimer la mise en page sélectionnée ?',
            copyPrefix:     'Copie de ',
            errSave:        'Erreur lors de l\'enregistrement : ',
            errRename:      'Erreur lors du renommage : ',
            errDelete:      'Erreur lors de la suppression : ',
            errCopy:        'Erreur lors de la copie : ',
            errDefault:     'Erreur lors de la définition par défaut : ',
            errLoad:        'Mise en page introuvable'
        },
        de: {
            placeholder:    '— Standardlayout —',
            menuBtn:        'Layout',
            load:           'Laden',
            save:           'Speichern',
            rename:         'Umbenennen',
            delete:         'Löschen',
            copy:           'Kopieren',
            setDefault:     'Als Standard setzen',
            badgePublic:    'Öffentlich',
            badgeDefault:   'Standard',
            promptSave:     'Name des Layouts:',
            promptRename:   'Neuer Name:',
            confirmDelete:  'Ausgewähltes Layout löschen?',
            copyPrefix:     'Kopie von ',
            errSave:        'Fehler beim Speichern: ',
            errRename:      'Fehler beim Umbenennen: ',
            errDelete:      'Fehler beim Löschen: ',
            errCopy:        'Fehler beim Kopieren: ',
            errDefault:     'Fehler beim Festlegen als Standard: ',
            errLoad:        'Layout nicht gefunden'
        }
    };

    function request(method, url, body) {
        var opts = {
            method: method,
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        };
        if (body !== undefined) {
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function (r) {
            if (!r.ok) return r.json().then(function(e) { return Promise.reject(e); });
            return r.json();
        });
    }

    window.RaccoonLayouts = {
        _layouts:            [],
        _pageKey:            pageKey,
        _locale:             _config.locale || '{{ $phpLocale }}',
        _i18n:               Object.assign({}, _i18n, _config.i18n || {}),
        _serverDefaultLayout: @json($serverDefaultLayout),

        t: function (key) {
            var locale = this._locale;
            var dict = this._i18n[locale] || this._i18n['en'] || {};
            return Object.prototype.hasOwnProperty.call(dict, key) ? dict[key] : key;
        },

        setLocale: function (locale) {
            this._locale = locale;
            this._dispatchEvent('raccoon-layouts:locale-changed', { locale: locale });
        },

        load: function (id) {
            var layout = this._layouts.find(function(l) { return l.id == id; });
            if (layout && layout.layout_data) {
                if (typeof setLayout === 'function') setLayout(layout.layout_data);
                return Promise.resolve(layout);
            }
            return Promise.reject(new Error(this.t('errLoad')));
        },

        save: function (name, isPublic) {
            var self = this;
            if (typeof getLayout !== 'function') {
                return Promise.reject(new Error('getLayout not available'));
            }
            return request('POST', routes.store, {
                name:        name,
                page_key:    pageKey,
                layout_data: getLayout(),
                is_public:   isPublic || false
            }).then(function (layout) {
                self._layouts.push(layout);
                self._dispatchEvent('raccoon-layouts:saved', layout);
                return layout;
            });
        },

        rename: function (id, name) {
            var self = this;
            return request('PUT', routes.update + id, { name: name })
                .then(function (layout) {
                    var idx = self._layouts.findIndex(function(l) { return l.id == id; });
                    if (idx !== -1) self._layouts[idx] = layout;
                    self._dispatchEvent('raccoon-layouts:renamed', layout);
                    return layout;
                });
        },

        delete: function (id) {
            var self = this;
            return request('DELETE', routes.destroy + id)
                .then(function () {
                    self._layouts = self._layouts.filter(function(l) { return l.id != id; });
                    self._dispatchEvent('raccoon-layouts:deleted', { id: id });
                });
        },

        setDefault: function (id) {
            var self = this;
            var url = typeof routes.setDefault === 'function'
                ? routes.setDefault(id)
                : routes.update + id + '/default';
            return request('POST', url)
                .then(function (layout) {
                    self._layouts.forEach(function(l) { l.is_default = (l.id == id); });
                    self._dispatchEvent('raccoon-layouts:default-set', layout);
                    return layout;
                });
        },

        copy: function (id) {
            var self = this;
            var url = typeof routes.copy === 'function'
                ? routes.copy(id)
                : routes.update + id + '/copy';
            return request('POST', url)
                .then(function (layout) {
                    self._layouts.push(layout);
                    self._dispatchEvent('raccoon-layouts:copied', layout);
                    return layout;
                });
        },

        _dispatchEvent: function (name, detail) {
            document.dispatchEvent(new CustomEvent(name, { detail: detail }));
        },

        _init: function () {
            var self = this;
            request('GET', routes.byPage + pageKey)
                .then(function (layouts) {
                    self._layouts = layouts;
                    self._dispatchEvent('raccoon-layouts:loaded', layouts);
                })
                .catch(function (err) {
                    console.warn('RaccoonLayouts: could not load layouts', err);
                });
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.RaccoonLayouts._init();
    });
})();
</script>
