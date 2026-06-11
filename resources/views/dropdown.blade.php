@php
    $wrapperClass = 'raccoon-layouts__wrapper';
    if (!empty($params['class'])) {
        $wrapperClass .= ' ' . $params['class'];
    }
@endphp
<div class="{{ $wrapperClass }}">
    <div class="raccoon-layouts__select-group">
        <select class="raccoon-layouts__select" id="raccoon-layouts-select">
            <option value="" class="raccoon-layouts__option raccoon-layouts__option--placeholder"></option>
        </select>
    </div>

    <div class="raccoon-layouts__badges" id="raccoon-layouts-badges" style="display:none;">
        <span class="raccoon-layouts__badge raccoon-layouts__badge--public"  id="raccoon-layouts-badge-public"></span>
        <span class="raccoon-layouts__badge raccoon-layouts__badge--default" id="raccoon-layouts-badge-default"></span>
    </div>

    <div class="raccoon-layouts__menu-container" id="raccoon-layouts-menu-container">
        <button type="button"
                class="raccoon-layouts__btn raccoon-layouts__btn--menu"
                id="raccoon-layouts-menu-trigger"
                aria-haspopup="true"
                aria-expanded="false">
            <span id="raccoon-layouts-menu-label"></span>
            <span class="raccoon-layouts__menu-arrow" aria-hidden="true">&#9662;</span>
        </button>

        <ul class="raccoon-layouts__menu" id="raccoon-layouts-menu" role="menu" style="display:none;">
            <li class="raccoon-layouts__menu-item raccoon-layouts__menu-item--save"
                id="raccoon-layouts-item-save" role="menuitem" tabindex="-1"></li>
            <li class="raccoon-layouts__menu-item raccoon-layouts__menu-item--rename raccoon-layouts__menu-item--needs-selection"
                id="raccoon-layouts-item-rename" role="menuitem" tabindex="-1"></li>
            <li class="raccoon-layouts__menu-item raccoon-layouts__menu-item--copy raccoon-layouts__menu-item--needs-selection"
                id="raccoon-layouts-item-copy" role="menuitem" tabindex="-1"></li>
            <li class="raccoon-layouts__menu-item raccoon-layouts__menu-item--set-default raccoon-layouts__menu-item--needs-selection"
                id="raccoon-layouts-item-set-default" role="menuitem" tabindex="-1"></li>
            <li class="raccoon-layouts__menu-divider" role="separator"></li>
            <li class="raccoon-layouts__menu-item raccoon-layouts__menu-item--delete raccoon-layouts__menu-item--danger raccoon-layouts__menu-item--needs-selection"
                id="raccoon-layouts-item-delete" role="menuitem" tabindex="-1"></li>
        </ul>
    </div>
</div>

<script>
(function () {
    function rl() { return window.RaccoonLayouts; }
    function t(key) { return rl() ? rl().t(key) : key; }

    var select      = document.getElementById('raccoon-layouts-select');
    var badgesEl    = document.getElementById('raccoon-layouts-badges');
    var badgePublic = document.getElementById('raccoon-layouts-badge-public');
    var badgeDef    = document.getElementById('raccoon-layouts-badge-default');
    var trigger     = document.getElementById('raccoon-layouts-menu-trigger');
    var menu        = document.getElementById('raccoon-layouts-menu');
    var menuLabel   = document.getElementById('raccoon-layouts-menu-label');
    var placeholder = select.options[0];

    // ── i18n init ─────────────────────────────────────────────────────────────

    function applyTranslations() {
        placeholder.textContent            = t('placeholder');
        menuLabel.textContent              = t('menuBtn');
        badgePublic.textContent            = t('badgePublic');
        badgeDef.textContent               = t('badgeDefault');
        document.getElementById('raccoon-layouts-item-save').textContent       = t('save');
        document.getElementById('raccoon-layouts-item-rename').textContent     = t('rename');
        document.getElementById('raccoon-layouts-item-copy').textContent       = t('copy');
        document.getElementById('raccoon-layouts-item-set-default').textContent= t('setDefault');
        document.getElementById('raccoon-layouts-item-delete').textContent     = t('delete');
    }

    // Run once RaccoonLayouts is defined (scripts.blade runs before DOMContentLoaded)
    document.addEventListener('DOMContentLoaded', applyTranslations);
    document.addEventListener('raccoon-layouts:locale-changed', applyTranslations);

    // ── Select helpers ─────────────────────────────────────────────────────────

    function buildOptions(layouts, keepSelection) {
        var currentValue = keepSelection ? select.value : null;
        while (select.options.length > 1) select.remove(1);

        var defaultId = null;
        layouts.forEach(function (layout) {
            var opt = document.createElement('option');
            opt.value = layout.id;
            opt.textContent = layout.name;
            opt.dataset.isPublic  = layout.is_public  ? '1' : '0';
            opt.dataset.isDefault = layout.is_default ? '1' : '0';
            opt.className = 'raccoon-layouts__option';
            select.appendChild(opt);
            if (layout.is_default) defaultId = String(layout.id);
        });

        if (currentValue) {
            select.value = currentValue;
        } else if (defaultId) {
            select.value = defaultId;
        }

        updateBadges();
        updateMenuItemStates();
    }

    function updateBadges() {
        var opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) {
            badgesEl.style.display = 'none';
            return;
        }
        var isPublic  = opt.dataset.isPublic  === '1';
        var isDefault = opt.dataset.isDefault === '1';
        badgePublic.style.display = isPublic  ? '' : 'none';
        badgeDef.style.display    = isDefault ? '' : 'none';
        badgesEl.style.display = (isPublic || isDefault) ? '' : 'none';
    }

    function updateMenuItemStates() {
        var hasSelection = !!select.value;
        document.querySelectorAll('.raccoon-layouts__menu-item--needs-selection')
            .forEach(function (el) {
                el.setAttribute('aria-disabled', hasSelection ? 'false' : 'true');
            });
    }

    select.addEventListener('change', function () {
        updateBadges();
        updateMenuItemStates();
        var id = select.value;
        if (!id) {
            if (typeof resetLayout === 'function') resetLayout();
        } else if (rl()) {
            rl().load(id);
        }
    });

    // ── Menu open/close ────────────────────────────────────────────────────────

    function openMenu() {
        menu.style.display = '';
        trigger.setAttribute('aria-expanded', 'true');
    }

    function closeMenu() {
        menu.style.display = 'none';
        trigger.setAttribute('aria-expanded', 'false');
    }

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.style.display === 'none' ? openMenu() : closeMenu();
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('raccoon-layouts-menu-container').contains(e.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });

    // ── Menu item actions ──────────────────────────────────────────────────────

    function onItem(id, fn) {
        document.getElementById(id).addEventListener('click', function () {
            if (this.getAttribute('aria-disabled') === 'true') return;
            closeMenu();
            fn();
        });
    }

    onItem('raccoon-layouts-item-save', function () {
        var name = prompt(t('promptSave'));
        if (!name) return;
        rl() && rl().save(name).catch(function(e) {
            alert(t('errSave') + (e.message || JSON.stringify(e)));
        });
    });

    onItem('raccoon-layouts-item-rename', function () {
        var id = select.value;
        if (!id) return;
        var name = prompt(t('promptRename'));
        if (!name) return;
        rl() && rl().rename(id, name).catch(function(e) {
            alert(t('errRename') + (e.message || JSON.stringify(e)));
        });
    });

    onItem('raccoon-layouts-item-copy', function () {
        var id = select.value;
        if (!id) return;
        rl() && rl().copy(id).catch(function(e) {
            alert(t('errCopy') + (e.message || JSON.stringify(e)));
        });
    });

    onItem('raccoon-layouts-item-set-default', function () {
        var id = select.value;
        if (!id) return;
        rl() && rl().setDefault(id).catch(function(e) {
            alert(t('errDefault') + (e.message || JSON.stringify(e)));
        });
    });

    onItem('raccoon-layouts-item-delete', function () {
        var id = select.value;
        if (!id) return;
        if (!confirm(t('confirmDelete'))) return;
        rl() && rl().delete(id).catch(function(e) {
            alert(t('errDelete') + (e.message || JSON.stringify(e)));
        });
    });

    // ── Event listeners ────────────────────────────────────────────────────────

    document.addEventListener('raccoon-layouts:loaded',      function (e) { buildOptions(e.detail, false); });
    document.addEventListener('raccoon-layouts:saved',       function ()  { if (rl()) buildOptions(rl()._layouts, true); });
    document.addEventListener('raccoon-layouts:renamed',     function ()  { if (rl()) buildOptions(rl()._layouts, true); });
    document.addEventListener('raccoon-layouts:copied',      function ()  { if (rl()) buildOptions(rl()._layouts, true); });
    document.addEventListener('raccoon-layouts:default-set', function ()  { if (rl()) buildOptions(rl()._layouts, true); });
    document.addEventListener('raccoon-layouts:deleted',     function ()  {
        if (rl()) buildOptions(rl()._layouts, false);
        select.value = '';
        updateBadges();
        updateMenuItemStates();
    });
})();
</script>
