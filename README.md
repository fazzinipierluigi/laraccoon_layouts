# laraccoon-layouts

Laravel package per il salvataggio e la gestione dei layout di [Raccoon Tables](https://github.com/fazzinipierluigi/raccoon-tables).

---

## 1. Installazione e pubblicazione asset

### Installazione via Composer

```bash
composer require fazzinipierluigi/laraccoon-layouts
```

Il service provider viene registrato automaticamente tramite Laravel package auto-discovery.

### Pubblicazione della configurazione

```bash
php artisan vendor:publish --tag=raccoon-layouts-config
```

Crea il file `config/raccoon_layouts.php` nella tua applicazione.

### Pubblicazione della migrazione

```bash
php artisan vendor:publish --tag=raccoon-layouts-migrations
php artisan migrate
```

### Pubblicazione delle view (opzionale, per personalizzare HTML/JS)

```bash
php artisan vendor:publish --tag=raccoon-layouts-views
```

Le view vengono copiate in `resources/views/vendor/raccoon-layouts/`.

---

## 2. Registrazione dello stack scripts nel layout base

La direttiva `@raccoonLayoutsScripts` emette il tag `<script>` inline. Se vuoi controllarne il posizionamento con lo stack di Blade, aggiungi nel tuo layout base (es. `layouts/app.blade.php`) prima di `</body>`:

```blade
@stack('scripts')
```

Poi nella tua view usa:

```blade
@push('scripts')
    @raccoonLayoutsScripts
@endpush
```

Oppure includi direttamente la direttiva dove preferisci:

```blade
@raccoonLayoutsScripts
```

---

## 3. Uso delle direttive con esempi HTML completi

### Esempio completo di una pagina con Raccoon Tables

```blade
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>La mia tabella</title>
</head>
<body>

    <!-- Pannello di gestione layout -->
    @raccoonLayoutsDropdown(['class' => 'my-toolbar'])

    <!-- Tabella Raccoon -->
    <div id="my-table"></div>

    <!-- Script Raccoon Tables (esempio) -->
    <script src="/path/to/raccoon-tables.js"></script>
    <script>
        var table = new RaccoonTable('#my-table', { /* opzioni */ });
    </script>

    <!-- Script di gestione layout (deve stare dopo Raccoon Tables) -->
    @raccoonLayoutsScripts

</body>
</html>
```

### @raccoonLayoutsDropdown con classe personalizzata

```blade
@raccoonLayoutsDropdown(['class' => 'toolbar__layouts'])
```

### @raccoonLayoutsScripts con pageKey manuale

```blade
<script>
    window.RaccoonLayoutsConfig = {
        pageKey: '{{ sha1("pagina-ordini") }}'
    };
</script>
@raccoonLayoutsScripts
```

---

## 4. Guida alla stilizzazione: classi BEM

Il dropdown emette markup con classi BEM `raccoon-layouts__*`. Nessuno stile inline è presente — personalizza liberamente con CSS.

| Classe | Elemento | Descrizione |
|--------|----------|-------------|
| `raccoon-layouts__wrapper` | `<div>` | Contenitore radice del pannello |
| `raccoon-layouts__select-group` | `<div>` | Wrapper attorno al `<select>` |
| `raccoon-layouts__select` | `<select>` | Dropdown per la selezione del layout |
| `raccoon-layouts__option` | `<option>` | Singola voce nel dropdown |
| `raccoon-layouts__option--placeholder` | `<option>` | Voce "Layout Standard" (nessun layout caricato) |
| `raccoon-layouts__actions` | `<div>` | Wrapper dei bottoni azione |
| `raccoon-layouts__btn` | `<button>` | Classe base per tutti i bottoni |
| `raccoon-layouts__btn--load` | `<button>` | Bottone Carica |
| `raccoon-layouts__btn--save` | `<button>` | Bottone Salva |
| `raccoon-layouts__btn--rename` | `<button>` | Bottone Rinomina |
| `raccoon-layouts__btn--delete` | `<button>` | Bottone Elimina |
| `raccoon-layouts__btn--copy` | `<button>` | Bottone Copia |
| `raccoon-layouts__btn--set-default` | `<button>` | Bottone Imposta Default |
| `raccoon-layouts__badges` | `<div>` | Contenitore badge (visibile solo se layout selezionato ha badge attivi) |
| `raccoon-layouts__badge` | `<span>` | Classe base badge |
| `raccoon-layouts__badge--public` | `<span>` | Badge "Pubblico" (visibile se `is_public = true`) |
| `raccoon-layouts__badge--default` | `<span>` | Badge "Default" (visibile se `is_default = true`) |

### Esempio CSS

```css
.raccoon-layouts__wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: #f5f5f5;
    border-radius: 4px;
}

.raccoon-layouts__select {
    min-width: 200px;
    padding: 4px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.raccoon-layouts__btn {
    padding: 4px 12px;
    border: 1px solid #999;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
}

.raccoon-layouts__btn:hover {
    background: #e8e8e8;
}

.raccoon-layouts__btn--delete {
    color: #c00;
    border-color: #c00;
}

.raccoon-layouts__badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75em;
    font-weight: bold;
}

.raccoon-layouts__badge--public {
    background: #dff0d8;
    color: #3c763d;
}

.raccoon-layouts__badge--default {
    background: #d9edf7;
    color: #31708f;
}
```

---

## 5. Guida al page_key

### Come viene generato

Il `page_key` è un hash SHA1 calcolato server-side al momento del rendering della direttiva `@raccoonLayoutsScripts`. La strategia dipende dal valore di `page_key_strategy` nella configurazione:

| Strategia | Sorgente del hash | Esempio sorgente |
|-----------|-------------------|-----------------|
| `url` (default) | URL completo della richiesta (`request()->url()`) | `https://example.com/ordini?tab=aperti` |
| `route_name` | Nome della route Laravel (`request()->route()->getName()`) | `ordini.index` |

La strategia `route_name` è consigliata quando l'URL contiene parametri variabili (query string, paginazione) ma si vuole condividere lo stesso pool di layout.

### Sovrascrivere il pageKey manualmente

Per usare un pageKey custom (utile per condividere layout tra URL diversi o per chiavi semantiche):

```blade
<script>
    window.RaccoonLayoutsConfig = {
        pageKey: '{{ sha1("chiave-semantica-custom") }}'
    };
</script>
@raccoonLayoutsScripts
```

Oppure con un hash già calcolato:

```blade
<script>
    window.RaccoonLayoutsConfig = {
        pageKey: 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3'
    };
</script>
@raccoonLayoutsScripts
```

> Il pageKey deve essere una stringa di 40 caratteri esadecimali (SHA1) per rispettare il vincolo della colonna `page_key VARCHAR(40)`.

---

## 6. Esempi integrazione con Raccoon Tables

### Inizializzazione base

```blade
<meta name="csrf-token" content="{{ csrf_token() }}">

@raccoonLayoutsDropdown

<div id="my-raccoon-table"></div>

<script src="/js/raccoon-tables.js"></script>
<script>
    var myTable = new RaccoonTable('#my-raccoon-table', {
        columns: [ /* ... */ ],
        data: [ /* ... */ ]
    });
</script>

@raccoonLayoutsScripts
```

> `@raccoonLayoutsScripts` deve essere incluso **dopo** l'inizializzazione di Raccoon Tables, perché utilizza le funzioni globali `getLayout()` e `loadLayout()` esposte dal plugin.

### Salvataggio programmatico

```javascript
// Salva il layout corrente con un nome specifico
RaccoonLayouts.save('Layout Mensile').then(function(layout) {
    console.log('Salvato con ID', layout.id);
});

// Salva come layout pubblico
RaccoonLayouts.save('Layout Condiviso', true);
```

### Risposta agli eventi

```javascript
document.addEventListener('raccoon-layouts:loaded', function(e) {
    console.log('Layout disponibili:', e.detail);
});

document.addEventListener('raccoon-layouts:saved', function(e) {
    alert('Layout "' + e.detail.name + '" salvato!');
});

document.addEventListener('raccoon-layouts:deleted', function(e) {
    console.log('Eliminato layout ID', e.detail.id);
});
```

### Override delle routes JS

```blade
<script>
    window.RaccoonLayoutsConfig = {
        pageKey: '{{ sha1(request()->route()->getName()) }}',
        routes: {
            byPage: '/api/v2/raccoon-layouts/page/',
            store:  '/api/v2/raccoon-layouts/store',
            update: '/api/v2/raccoon-layouts/',
            destroy:'/api/v2/raccoon-layouts/',
            setDefault: function(id) { return '/api/v2/raccoon-layouts/' + id + '/default'; },
            copy:       function(id) { return '/api/v2/raccoon-layouts/' + id + '/copy'; }
        }
    };
</script>
@raccoonLayoutsScripts
```

---

## 7. Configurazione middleware e autenticazione

Il file `config/raccoon_layouts.php`:

```php
return [
    'route_prefix' => 'raccoon-layouts',
    'middleware' => ['web', 'auth'],
    'user_model' => App\Models\User::class,
    'page_key_strategy' => 'url', // 'url' | 'route_name'
];
```

### Opzioni comuni

**Proteggere le route con un guard specifico:**
```php
'middleware' => ['web', 'auth:sanctum'],
```

**Aggiungere middleware di policy custom:**
```php
'middleware' => ['web', 'auth', 'verified'],
```

**Cambiare il modello User (es. con multi-tenancy):**
```php
'user_model' => App\Models\Admin::class,
```

**Prefisso route custom:**
```php
'route_prefix' => 'api/layouts',
```

---

## 8. API Reference

Tutti gli endpoint usano il prefisso configurato (default: `/raccoon-layouts`). Richiedono autenticazione e CSRF token nell'header `X-CSRF-TOKEN`.

---

### GET `/raccoon-layouts/page/{page_key}`

Lista i layout disponibili per la pagina: quelli dell'utente corrente + quelli pubblici di altri utenti.

**Risposta 200:**
```json
[
    {
        "id": 1,
        "user_id": 42,
        "name": "Layout Mensile",
        "is_public": false,
        "is_default": true
    },
    {
        "id": 7,
        "user_id": 15,
        "name": "Layout Condiviso",
        "is_public": true,
        "is_default": false
    }
]
```

---

### POST `/raccoon-layouts/store`

Salva un nuovo layout.

**Body:**
```json
{
    "name": "Il mio layout",
    "page_key": "a94a8fe5ccb19ba61c4c0873d391e987982fbbd3",
    "layout_data": { "columns": [], "sort": null },
    "is_public": false
}
```

**Risposta 201:**
```json
{
    "id": 12,
    "user_id": 42,
    "page_key": "a94a8fe5ccb19ba61c4c0873d391e987982fbbd3",
    "name": "Il mio layout",
    "layout_data": { "columns": [], "sort": null },
    "is_public": false,
    "is_default": false,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

---

### PUT `/raccoon-layouts/{id}`

Rinomina o aggiorna un layout esistente (solo proprietario).

**Body (tutti i campi opzionali):**
```json
{
    "name": "Nuovo nome",
    "layout_data": { "columns": [], "sort": "name" },
    "is_public": true
}
```

**Risposta 200:** oggetto layout aggiornato (stesso schema del POST).

---

### DELETE `/raccoon-layouts/{id}`

Elimina un layout (solo proprietario).

**Risposta 200:**
```json
{
    "message": "Deleted"
}
```

---

### POST `/raccoon-layouts/{id}/default`

Imposta il layout come default per l'utente corrente su quella pagina. Rimuove automaticamente il default precedente per la stessa coppia utente+pagina.

**Risposta 200:** oggetto layout con `is_default: true`.

---

### POST `/raccoon-layouts/{id}/copy`

Duplica un layout (proprio o pubblico) con nome "Copia di {nome originale}". La copia è sempre privata e non-default.

**Risposta 201:**
```json
{
    "id": 13,
    "user_id": 42,
    "page_key": "a94a8fe5ccb19ba61c4c0873d391e987982fbbd3",
    "name": "Copia di Layout Mensile",
    "layout_data": { "columns": [], "sort": null },
    "is_public": false,
    "is_default": false,
    "created_at": "2024-01-15T10:35:00.000000Z",
    "updated_at": "2024-01-15T10:35:00.000000Z"
}
```

---

### Errori comuni

| Codice | Causa |
|--------|-------|
| 401 | Utente non autenticato |
| 403 | Operazione non permessa (es. delete su layout altrui non pubblico) |
| 404 | Layout non trovato |
| 422 | Validazione fallita — body della risposta contiene `errors` |
