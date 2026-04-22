### USER
Создай проект и сделай его предварительную настройку и инициализацию. Сделай чтобы переходы между страницами не приводили к потере данных и их не нужно было заново запрашивать из API. Сделай красивый интерфейс (используй bootstrap UI), загрузчик (loader пока данные загружаются/фильтруются/сортируются). Дальше буду настраивать и разрабатывать отдельные страницы для отображения счетов (по номеру клиента), а так же блокировок ограничений и движений по счету.

### ASSISTANT
Отлично. Ниже приведена пошаговая инструкция по созданию и настройке проекта Yii2 + Vue 3 (SPA), с встроенным кэшированием данных, Bootstrap-интерфейсом и глобальным лоадером.

### 📦 1. Инициализация проекта (Yii2 Advanced Template)

```bash
composer create-project --prefer-dist yiisoft/yii2-app-advanced yii2-vue-spa
cd yii2-vue-spa
./init --env=Development
```

### 🌐 2. Настройка Vue 3 + Vite

Создаём директорию для фронтенда внутри `frontend`:
```bash
mkdir -p frontend/vue-src
cd frontend/vue-src
npm init -y
npm install vue vue-router pinia bootstrap axios
npm install -D vite @vitejs/plugin-vue
```

#### `frontend/vue-src/vite.config.js`
```js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  root: path.resolve(__dirname),
  build: {
    outDir: path.resolve(__dirname, '../web/dist'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'index.html')
    }
  },
  server: {
    port: 3000,
    open: true
  }
})
```

#### `frontend/vue-src/package.json`
Добавьте в `scripts`:
```json
"scripts": {
  "dev": "vite",
  "build": "vite build"
}
```

#### `frontend/vue-src/index.html`
```html
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Управление счетами</title>
</head>
<body>
  <div id="app"></div>
  <script type="module" src="/src/main.js"></script>
</body>
</html>
```

### ⚙️ 3. Vue Application: Роутер, Хранилище, Лоадер

#### `frontend/vue-src/src/main.js`
```js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')
```

#### `frontend/vue-src/src/router/index.js`
```js
import { createRouter, createWebHistory } from 'vue-router'
import AccountsView from '../views/AccountsView.vue'
import BlocksView from '../views/BlocksView.vue'
import RestrictionsView from '../views/RestrictionsView.vue'
import MovementsView from '../views/MovementsView.vue'

const routes = [
  { path: '/', redirect: '/accounts' },
  { path: '/accounts', name: 'accounts', component: AccountsView },
  { path: '/blocks', name: 'blocks', component: BlocksView },
  { path: '/restrictions', name: 'restrictions', component: RestrictionsView },
  { path: '/movements', name: 'movements', component: MovementsView },
]

const router = createRouter({
  history: createWebHistory('/'), // Требует fallback на сервере
  routes,
  scrollBehavior() { return { top: 0 } }
})

export default router
```

#### `frontend/vue-src/src/stores/dataStore.js` (Кэширование + Загрузка)
```js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useDataStore = defineStore('data', {
  state: () => ({
    cache: {},          // { cacheKey: data }
    loading: false,     // Глобальный флаг загрузки
    loadingContext: '', // Для отображения контекста (опционально)
  }),
  actions: {
    async fetchData(cacheKey, apiMethod, params) {
      // 1. Проверяем кэш
      if (this.cache[cacheKey] !== undefined) {
        return this.cache[cacheKey]
      }

      this.loading = true
      this.loadingContext = `Загрузка: ${apiMethod}...`
      try {
        // Вызов Yii2 прокси
        const { data } = await axios.post('/api/call', {
          method: apiMethod,
          params: params
        })

        if (data.errors && data.errors.length > 0) {
          throw new Error(data.errors[0].message)
        }

        // 2. Сохраняем в кэш
        this.cache[cacheKey] = data.data
        return data.data
      } catch (err) {
        console.error('API Error:', err)
        throw err
      } finally {
        this.loading = false
        this.loadingContext = ''
      }
    },
    clearCache(key) {
      if (key) delete this.cache[key]
      else this.cache = {}
    }
  }
})
```

#### `frontend/vue-src/src/components/AppLoader.vue`
```vue
<template>
  <Teleport to="body">
    <div v-if="isLoading" class="loader-overlay d-flex justify-content-center align-items-center">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Загрузка...</span>
      </div>
      <span class="ms-3 text-primary fw-semibold">{{ loadingContext }}</span>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useDataStore } from '../stores/dataStore'

const dataStore = useDataStore()
const isLoading = computed(() => dataStore.loading)
const loadingContext = computed(() => dataStore.loadingContext)
</script>

<style scoped>
.loader-overlay {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(2px);
  z-index: 9999; transition: opacity 0.3s;
}
</style>
```

#### `frontend/vue-src/src/App.vue`
```vue
<template>
  <div class="app-layout">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container">
        <router-link class="navbar-brand fw-bold" to="/">🏦 Учет Счетов</router-link>
        <div class="navbar-nav ms-auto">
          <router-link class="nav-link" to="/accounts" active-class="active text-white fw-bold">Счета</router-link>
          <router-link class="nav-link" to="/blocks" active-class="active text-white fw-bold">Блокировки</router-link>
          <router-link class="nav-link" to="/restrictions" active-class="active text-white fw-bold">Ограничения</router-link>
          <router-link class="nav-link" to="/movements" active-class="active text-white fw-bold">Движения</router-link>
        </div>
      </div>
    </nav>

    <main class="container py-4">
      <AppLoader />
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import AppLoader from './components/AppLoader.vue'
</script>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
body { background-color: #f8f9fa; }
</style>
```

#### Заглушки для страниц (`frontend/vue-src/src/views/`)
Создайте 4 файла: `AccountsView.vue`, `BlocksView.vue`, `RestrictionsView.vue`, `MovementsView.vue` с базовым содержимым:
```vue
<template>
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0">📄 Счета клиентов</h4>
    </div>
    <div class="card-body">
      <p class="text-muted">Страница в разработке. Данные будут подтягиваться из кэша Pinia.</p>
    </div>
  </div>
</template>
```
*(Аналогично для остальных, поменяв заголовок)*

### 🔗 4. Интеграция с Yii2

#### `frontend/config/main.php` (URL Manager)
Замените секцию `urlManager` на:
```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'api/call' => 'api/call',
        // Fallback для Vue Router (History Mode)
        '<alias:[\w\-]+>' => 'site/index',
    ],
],
```

#### `frontend/controllers/ApiController.php`
```php
<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionCall()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        
        if (!$request->isPost) {
            return ['errors' => [['code' => '405', 'message' => 'Method not allowed']]];
        }

        $payload = $request->post();
        $method = $payload['method'] ?? null;
        $params = $payload['params'] ?? [];

        if (!$method) {
            return ['errors' => [['code' => '400', 'message' => 'Missing API method']]];
        }

        try {
            // Универсальный вызов хранимой процедуры
            // Yii2 автоматически экранирует параметры
            $placeholders = [];
            $bindings = [];
            foreach ($params as $key => $value) {
                $placeholder = ":p_{$key}";
                $placeholders[] = $placeholder;
                $bindings[$placeholder] = $value;
            }

            $sql = "SELECT {$method}(" . implode(',', $placeholders) . ")";
            $command = Yii::$app->db->createCommand($sql);
            
            foreach ($bindings as $key => $val) {
                $command->bindValue($key, $val);
            }

            $rawResult = $command->queryScalar();
            return json_decode($rawResult, true) ?? ['data' => null];
            
        } catch (\Exception $e) {
            Yii::error("API Proxy Error: " . $e->getMessage());
            return ['errors' => [['code' => '500', 'message' => $e->getMessage()]]];
        }
    }
}
```

#### `frontend/controllers/SiteController.php`
Добавьте/измените `actionIndex`:
```php
public function actionIndex($alias = null)
{
    $distIndex = Yii::getAlias('@webroot/dist/index.html');
    if (file_exists($distIndex)) {
        return $this->renderFile($distIndex);
    }
    throw new \yii\web\NotFoundHttpException('Vue SPA не собрано. Выполните `npm run build` в `frontend/vue-src`.');
}
```

### 🚀 5. Сборка и Запуск

1. **Соберите Vue-приложение**:
   ```bash
   cd frontend/vue-src
   npm run build
   ```
   Файлы появятся в `frontend/web/dist/`.

2. **Настройте `.htaccess` в `frontend/web/`** (если используете Apache):
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^ index.php [L]
   ```

3. **Запустите Yii2**:
   ```bash
   php yii serve --port=8080
   ```
   Откройте `http://localhost:8080`.

### 💡 Ключевые особенности реализации

| Требование | Реализация |
|------------|------------|
| **SPA без перезагрузки** | `vue-router` в режиме `createWebHistory`. Переключение маршрутов не вызывает reload страницы. |
| **Без повторных запросов к API** | Pinia store `dataStore.cache` хранит результаты по уникальному ключу (например, `accounts:CUST123`). При повторном переходе данные берутся из памяти мгновенно. |
| **Глобальный лоадер** | Флаг `store.loading` управляет `<AppLoader />`, который показывается поверх контента при любом `fetchData()`. |
| **Bootstrap UI** | Подключен через npm, используется в навбаре, карточках, спиннере. Готов к расширению таблицами, модальными окнами и т.д. |
| **Интеграция с Yii2** | Vite собирает в `web/dist`, Yii2 отдает `index.html` для всех SPA-роутов, `ApiController` проксирует вызовы в PostgreSQL. |

### 🔜 Следующие шаги
Когда будете готовы разрабатывать страницы:
1. В `dataStore` вызывайте `fetchData('key', 'casaproci_api.get_accounts', { p_customer_no: '123' })`
2. Отображайте `store.cache['key']` в таблицах с фильтрацией/сортировкой на клиенте (данные уже в памяти).
3. При необходимости сброса кэша используйте `store.clearCache('key')`.

Проект полностью готов к наполнению бизнес-логикой. Если нужны примеры таблиц с сортировкой/фильтрацией или адаптация под конкретные API-сигнатуры из документации, напишите, и я предоставлю готовые компоненты.


### Счета клиента
Вот полный код компонента `AccountsView.vue`, который заменяет заглушку. Код написан на Vue 3 Composition API, использует Pinia для кэширования, Bootstrap 5 для стилей и реализует клиентскую сортировку/фильтрацию без повторных запросов к API.

`frontend/vue-src/src/views/AccountsView.vue`

```vue
<template>
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
      <h4 class="mb-0 text-primary fw-bold">📄 Счета клиента</h4>
      <span v-if="customerName" class="badge bg-primary fs-6 px-3 py-2">
        👤 {{ customerName }}
      </span>
    </div>
    
    <div class="card-body">
      <!-- Форма поиска -->
      <form @submit.prevent="fetchAccounts" class="row g-3 mb-4">
        <div class="col-md-4">
          <label for="customerNo" class="form-label fw-semibold">Номер клиента</label>
          <div class="input-group">
            <input 
              id="customerNo"
              type="text" 
              v-model="customerNo" 
              @input="onInput" 
              @keyup.enter="fetchAccounts"
              maxlength="8" 
              class="form-control form-control-lg" 
              placeholder="Введите 8 цифр"
              :class="{ 'is-invalid': inputError }"
              autocomplete="off"
            >
            <button type="submit" class="btn btn-primary btn-lg" :disabled="isLoading || inputError">
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
              {{ isLoading ? 'Загрузка...' : 'Найти' }}
            </button>
          </div>
          <div class="invalid-feedback d-block" v-if="inputError">{{ inputError }}</div>
        </div>
      </form>

      <!-- Таблица результатов -->
      <div v-if="accounts.length" class="table-responsive rounded border">
        <table class="table table-hover table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th v-for="col in columns" :key="col.key" 
                  class="sortable-header cursor-pointer text-nowrap"
                  @click="sortBy(col.key)">
                {{ col.label }}
                <span v-if="sortKey === col.key" class="ms-1 text-primary">
                  {{ sortDirection === 'asc' ? '▲' : '▼' }}
                </span>
              </th>
            </tr>
            <!-- Строка фильтров -->
            <tr>
              <td v-for="col in columns" :key="'f-' + col.key" class="p-1">
                <input 
                  type="text" 
                  v-model="filters[col.key]" 
                  class="form-control form-control-sm" 
                  placeholder="Фильтр..."
                >
              </td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(acc, idx) in filteredSortedAccounts" :key="idx">
              <td>{{ acc.branch || '-' }}</td>
              <td class="font-monospace">{{ acc.sysacc || '-' }}</td>
              <td class="font-monospace">{{ acc.account || '-' }}</td>
              <td><span class="badge" :class="acc.acc_stat === '0' ? 'bg-success' : 'bg-secondary'">{{ acc.acc_stat || '-' }}</span></td>
              <td>{{ formatDate(acc.open_date) }}</td>
              <td>{{ formatDate(acc.close_date) }}</td>
              <td class="text-end fw-bold text-dark">{{ formatMoney(acc.current_bal) }}</td>
            </tr>
            <tr v-if="filteredSortedAccounts.length === 0">
              <td :colspan="columns.length" class="text-center text-muted py-4">Нет записей, соответствующих фильтру</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Пустое состояние -->
      <div v-else-if="customerNo && !isLoading" class="alert alert-light border text-center py-4 mt-3">
        <p class="mb-0 text-muted">Счета не найдены. Проверьте номер клиента или обновите данные.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useDataStore } from '../stores/dataStore'

const store = useDataStore()

const customerNo = ref('')
const inputError = ref('')
const accounts = ref([])
const customerName = ref('')
const sortKey = ref('sysacc')
const sortDirection = ref('asc')
const filters = ref({})

const columns = [
  { key: 'branch', label: 'Филиал' },
  { key: 'sysacc', label: 'Системный №' },
  { key: 'account', label: 'Счет клиента' },
  { key: 'acc_stat', label: 'Статус' },
  { key: 'open_date', label: 'Открыт' },
  { key: 'close_date', label: 'Закрыт' },
  { key: 'current_bal', label: 'Остаток' }
]

// Инициализация фильтров
columns.forEach(c => filters.value[c.key] = '')

// Валидация ввода (только цифры, ровно 8)
const onInput = (e) => {
  const cleaned = e.target.value.replace(/\D/g, '')
  customerNo.value = cleaned
  if (cleaned.length > 0 && cleaned.length !== 8) {
    inputError.value = 'Номер клиента должен содержать ровно 8 цифр'
  } else if (cleaned.length === 8) {
    inputError.value = ''
  } else {
    inputError.value = ''
  }
}

// Загрузка данных через API
const fetchAccounts = async () => {
  if (inputError.value || customerNo.value.length !== 8) return
  
  try {
    const cacheKey = `accounts:${customerNo.value}`
    const res = await store.fetchData(cacheKey, 'casaproc_api.get_accounts', { p_customer_no: customerNo.value })
    
    if (res && Array.isArray(res.accounts_data)) {
      accounts.value = res.accounts_data
      customerName.value = res.customer_name || 'Клиент не указан'
    } else {
      accounts.value = []
      customerName.value = ''
    }
  } catch (err) {
    console.error('Ошибка загрузки счетов:', err)
    accounts.value = []
    customerName.value = ''
  }
}

// Вычисляемое свойство: фильтрация + сортировка
const filteredSortedAccounts = computed(() => {
  let data = [...accounts.value]
  
  // Фильтрация
  Object.keys(filters.value).forEach(key => {
    const val = filters.value[key].toLowerCase().trim()
    if (val) {
      data = data.filter(row => {
        const cell = String(row[key] ?? '').toLowerCase()
        return cell.includes(val)
      })
    }
  })
  
  // Сортировка
  if (sortKey.value) {
    data.sort((a, b) => {
      let valA = a[sortKey.value] ?? ''
      let valB = b[sortKey.value] ?? ''
      
      if (typeof valA === 'number' && typeof valB === 'number') {
        return sortDirection.value === 'asc' ? valA - valB : valB - valA
      }
      
      return sortDirection.value === 'asc'
        ? String(valA).localeCompare(String(valB), 'ru')
        : String(valB).localeCompare(String(valA), 'ru')
    })
  }
  
  return data
})

// Управление сортировкой
const sortBy = (key) => {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDirection.value = 'asc'
  }
}

// Форматирование
const formatMoney = (val) => val != null 
  ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 2 }).format(val) 
  : '-'

const formatDate = (val) => val ? new Date(val).toLocaleDateString('ru-RU') : '-'

// Состояние загрузки из глобального стора
const isLoading = computed(() => store.loading)
</script>

<style scoped>
.sortable-header {
  user-select: none;
  transition: background 0.2s;
}
.sortable-header:hover {
  background-color: #e9ecef;
}
.cursor-pointer {
  cursor: pointer;
}
.font-monospace {
  font-family: 'SF Mono', 'Menlo', 'Monaco', 'Consolas', monospace;
  font-size: 0.9rem;
}
</style>
```

### Ключевые особенности реализации:
1. Кэширование Pinia: При первом запросе данные сохраняются под ключом accounts:<номер>. При повторных переходах на страницу или изменении фильтров/сортировки API не вызывается.
2. Клиентская сортировка и фильтрация: Работает мгновенно через computed, не нагружает сервер. Фильтры по каждому столбцу, сортировка по клику на заголовок.
3. Валидация: Поле принимает только цифры, блокирует отправку, если введено не 8 символов.
4. UI/UX: Используются компоненты Bootstrap (card, table, badge, spinner). Глобальный лоадер (<AppLoader />) автоматически появляется при запросе API, кнопка "Найти" блокируется во избежание дублирования запросов.
5. Форматирование: Даты приводятся к ru-RU формату, суммы отображаются как валюта с разделителями.


## Пагинация для списка счетов

Вот обновленный код файла `frontend/vue-src/src/views/AccountsView.vue`. В него добавлена логика пагинации (разбиение по страницам), контролы навигации и обработчики для корректного переключения страниц при фильтрации.

```vue
<template>
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
      <h4 class="mb-0 text-primary fw-bold">📄 Счета клиента</h4>
      <span v-if="customerName" class="badge bg-primary fs-6 px-3 py-2">
        👤 {{ customerName }}
      </span>
    </div>
    
    <div class="card-body">
      <!-- Форма поиска -->
      <form @submit.prevent="fetchAccounts" class="row g-3 mb-4">
        <div class="col-md-4">
          <label for="customerNo" class="form-label fw-semibold">Номер клиента</label>
          <div class="input-group">
            <input 
              id="customerNo"
              type="text" 
              v-model="customerNo" 
              @input="onInput" 
              @keyup.enter="fetchAccounts"
              maxlength="8" 
              class="form-control form-control-lg" 
              placeholder="Введите 8 цифр"
              :class="{ 'is-invalid': inputError }"
              autocomplete="off"
            >
            <button type="submit" class="btn btn-primary btn-lg" :disabled="isLoading || inputError">
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
              {{ isLoading ? 'Загрузка...' : 'Найти' }}
            </button>
          </div>
          <div class="invalid-feedback d-block" v-if="inputError">{{ inputError }}</div>
        </div>
      </form>

      <!-- Таблица результатов -->
      <div v-if="accounts.length" class="table-responsive rounded border">
        <table class="table table-hover table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th v-for="col in columns" :key="col.key" 
                  class="sortable-header cursor-pointer text-nowrap"
                  @click="sortBy(col.key)">
                {{ col.label }}
                <span v-if="sortKey === col.key" class="ms-1 text-primary">
                  {{ sortDirection === 'asc' ? '▲' : '▼' }}
                </span>
              </th>
            </tr>
            <tr>
              <td v-for="col in columns" :key="'f-' + col.key" class="p-1">
                <input 
                  type="text" 
                  v-model="filters[col.key]" 
                  class="form-control form-control-sm" 
                  placeholder="Фильтр..."
                >
              </td>
            </tr>
          </thead>
          <tbody>
            <!-- Используем paginatedAccounts для отображения только текущей страницы -->
            <tr v-for="(acc, idx) in paginatedAccounts" :key="idx">
              <td>{{ acc.branch || '-' }}</td>
              <td class="font-monospace">{{ acc.sysacc || '-' }}</td>
              <td class="font-monospace">{{ acc.account || '-' }}</td>
              <td><span class="badge" :class="acc.acc_stat === '0' ? 'bg-success' : 'bg-secondary'">{{ acc.acc_stat || '-' }}</span></td>
              <td>{{ formatDate(acc.open_date) }}</td>
              <td>{{ formatDate(acc.close_date) }}</td>
              <td class="text-end fw-bold text-dark">{{ formatMoney(acc.current_bal) }}</td>
            </tr>
            <tr v-if="filteredSortedAccounts.length === 0">
               <td :colspan="columns.length" class="text-center text-muted py-4">Нет записей, соответствующих фильтру</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Пагинация -->
      <div v-if="totalPages > 1" class="d-flex justify-content-center align-items-center mt-3 gap-2">
         <span class="text-muted small">Страница {{ currentPage }} из {{ totalPages }}</span>
         <nav>
          <ul class="pagination mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="goToPage(1)">«</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">‹</a>
            </li>
            
            <li v-for="page in displayedPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
              <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
            </li>
            
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">›</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="goToPage(totalPages)">»</a>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Пустое состояние -->
      <div v-else-if="customerNo && !isLoading" class="alert alert-light border text-center py-4 mt-3">
        <p class="mb-0 text-muted">Счета не найдены. Проверьте номер клиента или обновите данные.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useDataStore } from '../stores/dataStore'

const store = useDataStore()

const customerNo = ref('')
const inputError = ref('')
const accounts = ref([])
const customerName = ref('')
const sortKey = ref('sysacc')
const sortDirection = ref('asc')
const filters = ref({})

const columns = [
  { key: 'branch', label: 'Филиал' },
  { key: 'sysacc', label: 'Системный №' },
  { key: 'account', label: 'Счет клиента' },
  { key: 'acc_stat', label: 'Статус' },
  { key: 'open_date', label: 'Открыт' },
  { key: 'close_date', label: 'Закрыт' },
  { key: 'current_bal', label: 'Остаток' }
]

columns.forEach(c => filters.value[c.key] = '')

// Пагинация
const perPage = 10
const currentPage = ref(1)

// Логика восстановления данных при загрузке страницы
onMounted(() => {
  if (store.searchMeta.customerNo) {
    customerNo.value = store.searchMeta.customerNo
    customerName.value = store.searchMeta.customerName
    const cacheKey = `accounts:${store.searchMeta.customerNo}`
    if (store.cache[cacheKey]) {
      const data = store.cache[cacheKey]
      if (data && Array.isArray(data.accounts_data)) {
        accounts.value = data.accounts_data
      }
    }
  }
})

const onInput = (e) => {
  const cleaned = e.target.value.replace(/\D/g, '')
  customerNo.value = cleaned
  if (cleaned.length > 0 && cleaned.length !== 8) {
    inputError.value = 'Номер клиента должен содержать ровно 8 цифр'
  } else if (cleaned.length === 8) {
    inputError.value = ''
  } else {
    inputError.value = ''
  }
}

const fetchAccounts = async () => {
  if (inputError.value || customerNo.value.length !== 8) return
  
  try {
    const cacheKey = `accounts:${customerNo.value}`
    const res = await store.fetchData(cacheKey, 'casaproc_api.get_accounts', { p_customer_no: customerNo.value })
    
    if (res && Array.isArray(res.accounts_data)) {
      accounts.value = res.accounts_data
      customerName.value = res.customer_name || 'Клиент не указан'
      store.updateSearchContext(customerNo.value, res.customer_name || '')
      currentPage.value = 1 // Сброс на первую страницу при новом поиске
    } else {
      accounts.value = []
      customerName.value = ''
      store.updateSearchContext(customerNo.value, '')
      currentPage.value = 1
    }
  } catch (err) {
    console.error('Ошибка загрузки счетов:', err)
    accounts.value = []
    customerName.value = ''
  }
}

const filteredSortedAccounts = computed(() => {
  let data = [...accounts.value]
  
  Object.keys(filters.value).forEach(key => {
    const val = filters.value[key].toLowerCase().trim()
    if (val) {
      data = data.filter(row => {
        const cell = String(row[key] ?? '').toLowerCase()
        return cell.includes(val)
      })
    }
  })
  
  if (sortKey.value) {
    data.sort((a, b) => {
      let valA = a[sortKey.value] ?? ''
      let valB = b[sortKey.value] ?? ''
      
      if (typeof valA === 'number' && typeof valB === 'number') {
        return sortDirection.value === 'asc' ? valA - valB : valB - valA
      }
      
      return sortDirection.value === 'asc'
        ? String(valA).localeCompare(String(valB), 'ru')
        : String(valB).localeCompare(String(valA), 'ru')
    })
  }
  
  return data
})

// Вычисляем общее количество страниц
const totalPages = computed(() => Math.ceil(filteredSortedAccounts.value.length / perPage))

// Вычисляем страницы для отображения (умное окно пагинации)
const displayedPages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  
  if (current <= 3) end = 5
  if (current >= total - 2) start = total - 4
  
  const pages = []
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

// Срез данных для текущей страницы
const paginatedAccounts = computed(() => {
  const start = (currentPage.value - 1) * perPage
  const end = start + perPage
  return filteredSortedAccounts.value.slice(start, end)
})

// Следим за изменением отфильтрованных данных, чтобы не оставаться на несуществующей странице
watch(filteredSortedAccounts, () => {
  if (currentPage.value > totalPages.value && totalPages.value > 0) {
    currentPage.value = totalPages.value
  } else if (totalPages.value === 0) {
    currentPage.value = 1
  }
})

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDirection.value = 'asc'
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const formatMoney = (val) => val != null 
  ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 2 }).format(val) 
  : '-'

const formatDate = (val) => val ? new Date(val).toLocaleDateString('ru-RU') : '-'

const isLoading = computed(() => store.loading)
</script>

<style scoped>
.sortable-header { user-select: none; transition: background 0.2s; }
.sortable-header:hover { background-color: #e9ecef; }
.cursor-pointer { cursor: pointer; }
.font-monospace { font-family: 'SF Mono', 'Menlo', 'Monaco', 'Consolas', monospace; font-size: 0.9rem; }
</style>
```

## Добавление формы о Состоянии счета

Вот полный код компонента `AccountStatusView.vue`, который реализует форму поиска, отображение состояния счета, табы с таблицами, фильтрацию, сортировку, пагинацию и сохранение данных между переходами.
`frontend/vue-src/src/views/AccountStatusView.vue`

```vue
<template>
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
      <h4 class="mb-0 text-primary fw-bold">📊 Состояние счета и аналитика</h4>
    </div>
    
    <div class="card-body">
      <!-- Поисковая форма -->
      <form @submit.prevent="handleSearch" class="row g-2 mb-4 align-items-end">
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Счет FCC12</label>
          <input v-model="search.sys_acc_no" type="text" class="form-control form-control-sm" placeholder="Системный №" required>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Счет ЦБ</label>
          <input v-model="search.cb_account" type="text" class="form-control form-control-sm" placeholder="CB Account">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Дата начала</label>
          <input v-model="search.date_from" type="date" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Дата окончания</label>
          <input v-model="search.date_to" type="date" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Внеш. система</label>
          <input v-model="search.ext_system" type="text" class="form-control form-control-sm" placeholder="Напр. PHUB">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Дебет/Кредит</label>
          <select v-model="search.drcr" class="form-select form-select-sm">
            <option value="">Все</option>
            <option value="D">Дебет (D)</option>
            <option value="C">Кредит (C)</option>
          </select>
        </div>
        <div class="col-md-1">
          <label class="form-label small fw-semibold">Сумма от</label>
          <input v-model.number="search.amount_from" type="number" class="form-control form-control-sm">
        </div>
        <div class="col-md-1">
          <label class="form-label small fw-semibold">Сумма до</label>
          <input v-model.number="search.amount_to" type="number" class="form-control form-control-sm">
        </div>
        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
          <button type="submit" class="btn btn-primary btn-sm px-4" :disabled="localLoading">
            <span v-if="localLoading" class="spinner-border spinner-border-sm me-1"></span>
            {{ localLoading ? 'Загрузка...' : 'Найти' }}
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm px-3" @click="resetSearch">Сбросить</button>
        </div>
      </form>

      <!-- Карточка состояния счета -->
      <div v-if="totalsData" class="card border-success mb-4">
        <div class="card-header bg-success bg-opacity-10 text-success fw-bold d-flex justify-content-between align-items-center">
          <span>🏦 Состояние счета: {{ totalsData.sys_acc_no || search.sys_acc_no }}</span>
          <span class="badge bg-success">{{ totalsData.ccy || 'RUB' }} | Статус: {{ totalsData.acc_stat || 'N/A' }}</span>
        </div>
        <div class="card-body">
          <div class="row g-3 text-center">
            <div v-for="(val, label) in totalsMapping" :key="label" class="col-md-2">
              <div class="small text-muted">{{ label }}</div>
              <div class="fs-5 fw-bold text-dark">{{ formatMoney(val) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Табы -->
      <div v-if="search.sys_acc_no || blocks.length || movements.length || restrictions.length" class="card mb-3 border-0 bg-light rounded p-2">
        <ul class="nav nav-tabs border-bottom-0">
          <li class="nav-item" v-for="tab in tabConfig" :key="tab.key">
            <button class="nav-link py-2 px-3 fw-semibold" 
                    :class="{ active: activeTab === tab.key, 'bg-white border border-bottom-0 rounded-top': activeTab === tab.key }"
                    @click="activeTab = tab.key">
              {{ tab.label }} 
              <span class="badge bg-secondary ms-1">{{ tab.items.length }}</span>
            </button>
          </li>
        </ul>

        <div class="card bg-white border rounded-top-0 p-3">
          <!-- Контент таба (таблица) -->
          <div v-for="tab in tabConfig" :key="tab.key" v-show="activeTab === tab.key">
            <div v-if="tab.items.length" class="table-responsive">
              <table class="table table-hover table-striped align-middle mb-0 small">
                <thead class="table-light">
                  <tr>
                    <th v-for="col in tab.columns" :key="col.key" 
                        class="cursor-pointer text-nowrap user-select-none" 
                        @click="handleSort(tab.key, col.key)">
                      {{ col.label }}
                      <span v-if="tab.sortKey === col.key" class="ms-1 text-primary">
                        {{ tab.sortDir === 'asc' ? '▲' : '▼' }}
                      </span>
                    </th>
                  </tr>
                  <tr>
                    <td v-for="col in tab.columns" :key="'f-'+col.key" class="p-1">
                      <input v-model="tab.filters[col.key]" type="text" class="form-control form-control-sm" placeholder="Фильтр...">
                    </td>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in getPaginatedData(tab.key)" :key="row.id || Math.random()">
                    <td v-for="col in tab.columns" :key="col.key + row.id">
                      <span v-if="col.type === 'money'">{{ formatMoney(row[col.key]) }}</span>
                      <span v-else-if="col.type === 'date'">{{ formatDate(row[col.key]) }}</span>
                      <span v-else-if="col.type === 'status'">
                        <span class="badge" :class="getStatusBadge(row[col.key])">{{ row[col.key] || '-' }}</span>
                      </span>
                      <span v-else>{{ row[col.key] || '-' }}</span>
                    </td>
                  </tr>
                  <tr v-if="getFilteredData(tab.key).length === 0">
                    <td :colspan="tab.columns.length" class="text-center text-muted py-3">Нет данных по фильтру</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Пагинация -->
            <div v-if="getTotalPages(tab.key) > 1" class="d-flex justify-content-center align-items-center mt-3 gap-2">
              <span class="text-muted small">Страница {{ tab.currentPage }} из {{ getTotalPages(tab.key) }}</span>
              <nav>
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item" :class="{ disabled: tab.currentPage === 1 }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab.key, 1)">«</a>
                  </li>
                  <li class="page-item" :class="{ disabled: tab.currentPage === 1 }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab.key, tab.currentPage - 1)">‹</a>
                  </li>
                  <li v-for="p in getDisplayedPages(tab.key)" :key="p" class="page-item" :class="{ active: p === tab.currentPage }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab.key, p)">{{ p }}</a>
                  </li>
                  <li class="page-item" :class="{ disabled: tab.currentPage === getTotalPages(tab.key) }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab.key, tab.currentPage + 1)">›</a>
                  </li>
                  <li class="page-item" :class="{ disabled: tab.currentPage === getTotalPages(tab.key) }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab.key, getTotalPages(tab.key))">»</a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useDataStore } from '../stores/dataStore'

const store = useDataStore()
const localLoading = ref(false)
const activeTab = ref('blocks')

// Параметры поиска
const search = reactive({
  sys_acc_no: '',
  cb_account: '',
  date_from: '',
  date_to: '',
  ext_system: '',
  drcr: '',
  amount_from: null,
  amount_to: null
})

// Данные
const totalsData = ref(null)
const blocks = ref([])
const movements = ref([])
const restrictions = ref([])

// Конфигурация табов
const createTabState = (items) => reactive({
  items,
  filters: {},
  sortKey: '',
  sortDir: 'asc',
  currentPage: 1,
  perPage: 8
})

const blocksState = createTabState(blocks)
const movementsState = createTabState(movements)
const restrictionsState = createTabState(restrictions)

const tabConfig = [
  { 
    key: 'blocks', label: '🔒 Блокировки', 
    state: blocksState, 
    columns: [
      { key: 'block_id', label: 'ID блокировки' },
      { key: 'amount', label: 'Сумма', type: 'money' },
      { key: 'block_type', label: 'Тип' },
      { key: 'block_status', label: 'Статус', type: 'status' },
      { key: 'ext_system', label: 'Внеш. система' },
      { key: 'movement_dt', label: 'Дата движения', type: 'date' },
      { key: 'priority', label: 'Приоритет' },
      { key: 'cbr_priority', label: 'Приоритет ЦБ' }
    ]
  },
  { 
    key: 'movements', label: '💳 Движения', 
    state: movementsState, 
    columns: [
      { key: 'id', label: 'ID' },
      { key: 'amount', label: 'Сумма', type: 'money' },
      { key: 'dr_cr_ind', label: 'Д/К', type: 'status' },
      { key: 'value_date', label: 'Дата валютир.', type: 'date' },
      { key: 'txn_date', label: 'Дата транз.', type: 'date' },
      { key: 'doc_num', label: 'Документ' },
      { key: 'ext_system', label: 'Внеш. система' },
      { key: 'cb_account', label: 'Счет ЦБ' }
    ]
  },
  { 
    key: 'restrictions', label: '⚠️ Ограничения', 
    state: restrictionsState, 
    columns: [
      { key: 'id', label: 'ID' },
      { key: 'amount', label: 'Сумма', type: 'money' },
      { key: 'restr_type', label: 'Тип' },
      { key: 'status', label: 'Статус', type: 'status' },
      { key: 'initiator', label: 'Инициатор' },
      { key: 'ext_system', label: 'Внеш. система' },
      { key: 'start_date', label: 'Начало', type: 'date' },
      { key: 'expiry_date', label: 'Истекает', type: 'date' },
      { key: 'exec_proc_number', label: 'Исп. производство' }
    ]
  }
]

// Маппинг полей для отображения totals
const totalsMapping = computed(() => ({
  'Откр. остаток': totalsData.value?.open_bal,
  'Закр. остаток': totalsData.value?.close_bal,
  'Доступно': totalsData.value?.avail_cur_bal,
  'Овердрафт': totalsData.value?.cur_unutil_ovd,
  'Конф. блокировки': totalsData.value?.total_confbl,
  'Неконф. блокировки': totalsData.value?.total_unconfbl,
  'Налоги': totalsData.value?.total_tax,
  'Судебные': totalsData.value?.total_bailiffs,
  'Кр. оборот': totalsData.value?.cr_turn,
  'Дб. оборот': totalsData.value?.dr_turn
}))

// Восстановление данных при загрузке страницы
onMounted(() => {
  if (store.searchMeta.accStatusSearch) {
    Object.assign(search, store.searchMeta.accStatusSearch)
    const cacheKey = `acc_status:${JSON.stringify(search)}`
    const cached = store.cache[cacheKey]
    if (cached) {
      totalsData.value = cached.totals
      blocks.value = cached.blocks
      movements.value = cached.movements
      restrictions.value = cached.restrictions
    }
  }
})

// Сброс формы
const resetSearch = () => {
  Object.assign(search, { sys_acc_no: '', cb_account: '', date_from: '', date_to: '', ext_system: '', drcr: '', amount_from: null, amount_to: null })
  totalsData.value = null; blocks.value = []; movements.value = []; restrictions.value = []
}

// Основной поиск
const handleSearch = async () => {
  if (!search.sys_acc_no) return
  localLoading.value = true
  
  try {
    const cacheKey = `acc_status:${JSON.stringify(search)}`
    const cached = store.cache[cacheKey]
    
    if (cached) {
      totalsData.value = cached.totals
      blocks.value = cached.blocks
      movements.value = cached.movements
      restrictions.value = cached.restrictions
    } else {
      // Параллельные запросы к 4 API
      const [resTotals, resBlocks, resMovements, resRestrictions] = await Promise.allSettled([
        axios.post('/api/call', { method: 'casaproc_api.get_acc_totals', params: { p_sys_acc_no: search.sys_acc_no, p_from_date: search.date_from || null, p_to_date: search.date_to || null } }),
        axios.post('/api/call', { method: 'casaproc_api.get_blocks', params: { sys_acc_no: search.sys_acc_no, from_date: search.date_from, to_date: search.date_to, from_amount: search.amount_from, to_amount: search.amount_to, ext_system: search.ext_system } }),
        axios.post('/api/call', { method: 'casaproc_api.get_movements', params: { sys_acc_no: search.sys_acc_no, from_date: search.date_from, to_date: search.date_to, amt_from: search.amount_from, amt_to: search.amount_to, drcr: search.drcr, ext_system: search.ext_system } }),
        axios.post('/api/call', { method: 'casaproc_api.get_restrictions', params: { sysacc: search.sys_acc_no, start_date_from: search.date_from, start_date_to: search.date_to, closure_date_from: search.date_from, closure_date_to: search.date_to, restr_amt: search.amount_from || search.amount_to, ext_system: search.ext_system } })
      ])

      // Обработка результатов
      totalsData.value = resTotals.status === 'fulfilled' && resTotals.value.data.data ? resTotals.value.data.data : {}
      blocks.value = resBlocks.status === 'fulfilled' && resBlocks.value.data.data ? resBlocks.value.data.data : []
      movements.value = resMovements.status === 'fulfilled' && resMovements.value.data.data ? resMovements.value.data.data : []
      restrictions.value = resRestrictions.status === 'fulfilled' && resRestrictions.value.data.data ? resRestrictions.value.data.data : []

      // Сохраняем в кэш и мета-данные
      store.cache[cacheKey] = { totals: totalsData.value, blocks: blocks.value, movements: movements.value, restrictions: restrictions.value }
      store.searchMeta.accStatusSearch = { ...search }
    }
  } catch (err) {
    console.error('Ошибка загрузки:', err)
  } finally {
    localLoading.value = false
    // Сброс пагинации при новом поиске
    tabConfig.forEach(t => t.state.currentPage = 1)
  }
}

// Фильтрация
const getFilteredData = (tabKey) => {
  const tab = tabConfig.find(t => t.key === tabKey)
  let data = [...tab.state.items]
  
  Object.entries(tab.state.filters).forEach(([key, val]) => {
    if (val) {
      const filter = String(val).toLowerCase()
      data = data.filter(row => String(row[key] ?? '').toLowerCase().includes(filter))
    }
  })
  
  // Сортировка
  if (tab.state.sortKey) {
    data.sort((a, b) => {
      const vA = a[tab.state.sortKey] ?? ''
      const vB = b[tab.state.sortKey] ?? ''
      if (typeof vA === 'number' && typeof vB === 'number') {
        return tab.state.sortDir === 'asc' ? vA - vB : vB - vA
      }
      return tab.state.sortDir === 'asc' ? String(vA).localeCompare(String(vB), 'ru') : String(vB).localeCompare(String(vA), 'ru')
    })
  }
  return data
}

// Пагинация
const getPaginatedData = (tabKey) => {
  const tab = tabConfig.find(t => t.key === tabKey)
  const filtered = getFilteredData(tabKey)
  const start = (tab.state.currentPage - 1) * tab.state.perPage
  return filtered.slice(start, start + tab.state.perPage)
}

const getTotalPages = (tabKey) => Math.ceil(getFilteredData(tabKey).length / tabConfig.find(t => t.key === tabKey).state.perPage)
const getDisplayedPages = (tabKey) => {
  const tab = tabConfig.find(t => t.key === tabKey)
  const total = getTotalPages(tabKey)
  const current = tab.state.currentPage
  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1)
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
}

const goToPage = (tabKey, page) => {
  const tab = tabConfig.find(t => t.key === tabKey)
  if (page >= 1 && page <= getTotalPages(tabKey)) tab.state.currentPage = page
}

// Обработчики
const handleSort = (tabKey, key) => {
  const tab = tabConfig.find(t => t.key === tabKey)
  if (tab.state.sortKey === key) tab.state.sortDir = tab.state.sortDir === 'asc' ? 'desc' : 'asc'
  else { tab.state.sortKey = key; tab.state.sortDir = 'asc' }
}

const resetPage = () => tabConfig.forEach(t => t.state.currentPage = 1)
watch(() => tabConfig.map(t => JSON.stringify(t.state.filters)), resetPage)

// Форматтеры
const formatMoney = (v) => v != null ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 2 }).format(v) : '-'
const formatDate = (v) => v ? new Date(v).toLocaleDateString('ru-RU') : '-'
const getStatusBadge = (v) => {
  if (v === '0' || v === 'A' || v === 'C') return 'bg-success'
  if (v === '1' || v === 'I') return 'bg-warning text-dark'
  if (v === 'D') return 'bg-danger'
  return 'bg-secondary'
}
</script>

<style scoped>
.user-select-none { user-select: none; }
.cursor-pointer { cursor: pointer; }
.nav-link.active { font-weight: 600; color: #0d6efd !important; border-color: #dee2e6 #dee2e6 #fff !important; }
</style>
```


## Ключевые особенности:
1. Сохранение данных между переходами: Используется store.cache и store.searchMeta. При возврате на страницу данные восстанавливаются мгновенно без повторных запросов к API.
2. Параллельная загрузка: 4 API вызываются одновременно через Promise.allSettled. Ошибка в одном из них не блокирует остальные.
3. Умная фильтрация и сортировка: Каждый таб имеет независимые фильтры по столбцам. Сортировка работает по клику на заголовок. При изменении фильтров пагинация автоматически сбрасывается на 1 страницу.
4. Динамические табы и таблицы: Конфигурация столбцов вынесена в массив tabConfig, что позволяет легко добавлять новые вкладки или менять поля без дублирования кода.
5. Адаптивный Bootstrap UI: Форма поиска компактна, таблица оборачивается в table-responsive, бейджи статусов подсвечиваются автоматически.
6. Форматирование: Даты, суммы и статусы приводятся к читаемому виду согласно банковской логике.

Добавьте маршрут в `router/index.js` и компонент готов к работе. Если потребуется маппинг дополнительных полей из ответа API или интеграция с экспортом в Excel, напишите.

## Ошибка при обращении к tab.items.length

Ошибка возникает из-за рассинхронизации структуры данных: в шаблоне используется `tab.items`, но в JS объекты табов хранят массивы внутри `tab.state.items`. Кроме того, для предотвращения проблем с реактивностью Vue 3 при обновлении данных, я упростил архитектуру состояния и строго привязал параметры API к вашей документации (учтены точные имена полей из `get_acc_totals.md`, `get_blocks.md` и др.).

```vue
<template>
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
      <h4 class="mb-0 text-primary fw-bold">📊 Состояние счета и аналитика</h4>
    </div>
    
    <div class="card-body">
      <!-- Поисковая форма -->
      <form @submit.prevent="handleSearch" class="row g-2 mb-4 align-items-end">
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Счет FCC12 *</label>
          <input v-model="search.sys_acc_no" type="text" class="form-control form-control-sm" placeholder="Системный №" required>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Счет ЦБ</label>
          <input v-model="search.cb_account" type="text" class="form-control form-control-sm" placeholder="CB Account">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Дата начала</label>
          <input v-model="search.date_from" type="date" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Дата окончания</label>
          <input v-model="search.date_to" type="date" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Внеш. система</label>
          <input v-model="search.ext_system" type="text" class="form-control form-control-sm" placeholder="Напр. PHUB">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Дебет/Кредит</label>
          <select v-model="search.drcr" class="form-select form-select-sm">
            <option value="">Все</option>
            <option value="D">Дебет (D)</option>
            <option value="C">Кредит (C)</option>
          </select>
        </div>
        <div class="col-md-1">
          <label class="form-label small fw-semibold">Сумма от</label>
          <input v-model.number="search.amount_from" type="number" class="form-control form-control-sm">
        </div>
        <div class="col-md-1">
          <label class="form-label small fw-semibold">Сумма до</label>
          <input v-model.number="search.amount_to" type="number" class="form-control form-control-sm">
        </div>
        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
          <button type="submit" class="btn btn-primary btn-sm px-4" :disabled="localLoading || !search.sys_acc_no">
            <span v-if="localLoading" class="spinner-border spinner-border-sm me-1"></span>
            {{ localLoading ? 'Загрузка...' : 'Найти' }}
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm px-3" @click="resetSearch">Сбросить</button>
        </div>
      </form>

      <!-- Карточка состояния счета -->
      <div v-if="totalsData && Object.keys(totalsData).length" class="card border-success mb-4">
        <div class="card-header bg-success bg-opacity-10 text-success fw-bold d-flex justify-content-between align-items-center">
          <span>🏦 Состояние счета: {{ totalsData.sys_acc_no || search.sys_acc_no }}</span>
          <span class="badge bg-success">{{ totalsData.ccy || 'RUB' }} | Статус: {{ totalsData.acc_stat || 'N/A' }}</span>
        </div>
        <div class="card-body">
          <div class="row g-3 text-center">
            <div v-for="(val, label) in totalsMapping" :key="label" class="col-md-2">
              <div class="small text-muted">{{ label }}</div>
              <div class="fs-5 fw-bold text-dark">{{ formatMoney(val) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Табы -->
      <div v-if="activeTab || blocksState.items.length || movementsState.items.length || restrictionsState.items.length" class="card mb-3 border-0 bg-light rounded p-2">
        <ul class="nav nav-tabs border-bottom-0">
          <li class="nav-item" v-for="tab in tabConfig" :key="tab.key">
            <button class="nav-link py-2 px-3 fw-semibold" 
                    :class="{ active: activeTab === tab.key, 'bg-white border border-bottom-0 rounded-top': activeTab === tab.key }"
                    @click="activeTab = tab.key">
              {{ tab.label }} 
              <span class="badge bg-secondary ms-1">{{ tab.state.items.length }}</span>
            </button>
          </li>
        </ul>

        <div class="card bg-white border rounded-top-0 p-3">
          <div v-for="tab in tabConfig" :key="tab.key" v-show="activeTab === tab.key">
            <div v-if="tab.state.items.length" class="table-responsive">
              <table class="table table-hover table-striped align-middle mb-0 small">
                <thead class="table-light">
                  <tr>
                    <th v-for="col in tab.columns" :key="col.key" 
                        class="cursor-pointer text-nowrap user-select-none" 
                        @click="handleSort(tab, col.key)">
                      {{ col.label }}
                      <span v-if="tab.state.sortKey === col.key" class="ms-1 text-primary">
                        {{ tab.state.sortDir === 'asc' ? '▲' : '▼' }}
                      </span>
                    </th>
                  </tr>
                  <tr>
                    <td v-for="col in tab.columns" :key="'f-'+col.key" class="p-1">
                      <input v-model="tab.state.filters[col.key]" type="text" class="form-control form-control-sm" placeholder="Фильтр...">
                    </td>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in getPaginatedData(tab)" :key="row.id || Math.random()">
                    <td v-for="col in tab.columns" :key="col.key + (row.id || Math.random())">
                      <span v-if="col.type === 'money'">{{ formatMoney(row[col.key]) }}</span>
                      <span v-else-if="col.type === 'date'">{{ formatDate(row[col.key]) }}</span>
                      <span v-else-if="col.type === 'status'">
                        <span class="badge" :class="getStatusBadge(row[col.key])">{{ row[col.key] || '-' }}</span>
                      </span>
                      <span v-else>{{ row[col.key] || '-' }}</span>
                    </td>
                  </tr>
                  <tr v-if="getFilteredData(tab).length === 0">
                    <td :colspan="tab.columns.length" class="text-center text-muted py-3">Нет данных по фильтру</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Пагинация -->
            <div v-if="getTotalPages(tab) > 1" class="d-flex justify-content-center align-items-center mt-3 gap-2">
              <span class="text-muted small">Страница {{ tab.state.currentPage }} из {{ getTotalPages(tab) }}</span>
              <nav>
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item" :class="{ disabled: tab.state.currentPage === 1 }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab, 1)">«</a>
                  </li>
                  <li class="page-item" :class="{ disabled: tab.state.currentPage === 1 }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab, tab.state.currentPage - 1)">‹</a>
                  </li>
                  <li v-for="p in getDisplayedPages(tab)" :key="p" class="page-item" :class="{ active: p === tab.state.currentPage }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab, p)">{{ p }}</a>
                  </li>
                  <li class="page-item" :class="{ disabled: tab.state.currentPage === getTotalPages(tab) }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab, tab.state.currentPage + 1)">›</a>
                  </li>
                  <li class="page-item" :class="{ disabled: tab.state.currentPage === getTotalPages(tab) }">
                    <a class="page-link" href="#" @click.prevent="goToPage(tab, getTotalPages(tab))">»</a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useDataStore } from '../stores/dataStore'

const store = useDataStore()
const localLoading = ref(false)
const activeTab = ref('blocks')

// Параметры поиска
const search = reactive({
  sys_acc_no: '',
  cb_account: '',
  date_from: '',
  date_to: '',
  ext_system: '',
  drcr: '',
  amount_from: null,
  amount_to: null
})

// Данные
const totalsData = ref(null)

// Конфигурация табов
const createTabState = () => reactive({
  items: [],
  filters: {},
  sortKey: '',
  sortDir: 'asc',
  currentPage: 1,
  perPage: 8
})

const blocksState = createTabState()
const movementsState = createTabState()
const restrictionsState = createTabState()

const tabConfig = [
  { 
    key: 'blocks', label: '🔒 Блокировки', 
    state: blocksState, 
    columns: [
      { key: 'block_id', label: 'ID блокировки' },
      { key: 'amount', label: 'Сумма', type: 'money' },
      { key: 'block_type', label: 'Тип' },
      { key: 'block_status', label: 'Статус', type: 'status' },
      { key: 'ext_system', label: 'Внеш. система' },
      { key: 'movement_dt', label: 'Дата движения', type: 'date' },
      { key: 'priority', label: 'Приоритет' },
      { key: 'cbr_priority', label: 'Приоритет ЦБ' }
    ]
  },
  { 
    key: 'movements', label: '💳 Движения', 
    state: movementsState, 
    columns: [
      { key: 'id', label: 'ID' },
      { key: 'amount', label: 'Сумма', type: 'money' },
      { key: 'dr_cr_ind', label: 'Д/К', type: 'status' },
      { key: 'value_date', label: 'Дата валютир.', type: 'date' },
      { key: 'txn_date', label: 'Дата транз.', type: 'date' },
      { key: 'doc_num', label: 'Документ' },
      { key: 'ext_system', label: 'Внеш. система' },
      { key: 'cb_account', label: 'Счет ЦБ' }
    ]
  },
  { 
    key: 'restrictions', label: '⚠️ Ограничения', 
    state: restrictionsState, 
    columns: [
      { key: 'id', label: 'ID' },
      { key: 'amount', label: 'Сумма', type: 'money' },
      { key: 'restr_type', label: 'Тип' },
      { key: 'record_stat', label: 'Статус', type: 'status' },
      { key: 'initiator', label: 'Инициатор' },
      { key: 'ext_system', label: 'Внеш. система' },
      { key: 'start_date', label: 'Начало', type: 'date' },
      { key: 'expiry_date', label: 'Истекает', type: 'date' },
      { key: 'exec_proc_number', label: 'Исп. производство' }
    ]
  }
]

// Маппинг полей для отображения totals
const totalsMapping = computed(() => ({
  'Откр. остаток': totalsData.value?.open_bal,
  'Закр. остаток': totalsData.value?.close_bal,
  'Доступно': totalsData.value?.avail_cur_bal,
  'Овердрафт': totalsData.value?.cur_unutil_ovd,
  'Конф. блокировки': totalsData.value?.total_confbl,
  'Неконф. блокировки': totalsData.value?.total_unconfbl,
  'Налоги': totalsData.value?.total_tax,
  'Судебные': totalsData.value?.total_bailiffs,
  'Кр. оборот': totalsData.value?.cr_turn,
  'Дб. оборот': totalsData.value?.dr_turn
}))

// Восстановление данных при загрузке страницы
onMounted(() => {
  if (store.searchMeta.accStatusSearch) {
    Object.assign(search, store.searchMeta.accStatusSearch)
    const cacheKey = `acc_status:${JSON.stringify(search)}`
    const cached = store.cache[cacheKey]
    if (cached) {
      totalsData.value = cached.totals
      blocksState.items = cached.blocks || []
      movementsState.items = cached.movements || []
      restrictionsState.items = cached.restrictions || []
    }
  }
})

// Сброс формы
const resetSearch = () => {
  Object.assign(search, { sys_acc_no: '', cb_account: '', date_from: '', date_to: '', ext_system: '', drcr: '', amount_from: null, amount_to: null })
  totalsData.value = null
  blocksState.items = []; movementsState.items = []; restrictionsState.items = []
}

// Основной поиск
const handleSearch = async () => {
  if (!search.sys_acc_no) return
  localLoading.value = true
  
  try {
    const cacheKey = `acc_status:${JSON.stringify(search)}`
    const cached = store.cache[cacheKey]
    
    if (cached) {
      totalsData.value = cached.totals
      blocksState.items = cached.blocks || []
      movementsState.items = cached.movements || []
      restrictionsState.items = cached.restrictions || []
    } else {
      // Параллельные запросы к 4 API (параметры строго по документации)
      const [resTotals, resBlocks, resMovements, resRestrictions] = await Promise.allSettled([
        axios.post('/api/call', { method: 'casaproc_api.get_acc_totals', params: { p_sys_acc_no: search.sys_acc_no, p_from_date: search.date_from || null, p_to_date: search.date_to || null } }),
        axios.post('/api/call', { method: 'casaproc_api.get_blocks', params: { sys_acc_no: search.sys_acc_no, from_date: search.date_from || null, to_date: search.date_to || null, from_amount: search.amount_from, to_amount: search.amount_to, ext_system: search.ext_system || null } }),
        axios.post('/api/call', { method: 'casaproc_api.get_movements', params: { sys_acc_no: search.sys_acc_no, from_date: search.date_from || null, to_date: search.date_to || null, amt_from: search.amount_from, amt_to: search.amount_to, drcr: search.drcr || null, ext_system: search.ext_system || null } }),
        axios.post('/api/call', { method: 'casaproc_api.get_restrictions', params: { sysacc: search.sys_acc_no, start_date_from: search.date_from || null, start_date_to: search.date_to || null, closure_date_from: search.date_from || null, closure_date_to: search.date_to || null, restr_amt: search.amount_from || search.amount_to, ext_system: search.ext_system || null } })
      ])

      // Извлечение данных с защитой от ошибок
      totalsData.value = resTotals.status === 'fulfilled' ? (resTotals.value.data.data || {}) : {}
      blocksState.items = resBlocks.status === 'fulfilled' ? (resBlocks.value.data.data || []) : []
      movementsState.items = resMovements.status === 'fulfilled' ? (resMovements.value.data.data || []) : []
      restrictionsState.items = resRestrictions.status === 'fulfilled' ? (resRestrictions.value.data.data || []) : []

      // Сохраняем в кэш и мета-данные
      store.cache[cacheKey] = { totals: totalsData.value, blocks: blocksState.items, movements: movementsState.items, restrictions: restrictionsState.items }
      store.searchMeta.accStatusSearch = { ...search }
    }
  } catch (err) {
    console.error('Ошибка загрузки:', err)
  } finally {
    localLoading.value = false
    // Сброс пагинации при новом поиске
    tabConfig.forEach(t => t.state.currentPage = 1)
  }
}

// Фильтрация и сортировка
const getFilteredData = (tab) => {
  let data = [...tab.state.items]
  
  Object.entries(tab.state.filters).forEach(([key, val]) => {
    if (val) {
      const filter = String(val).toLowerCase()
      data = data.filter(row => String(row[key] ?? '').toLowerCase().includes(filter))
    }
  })
  
  if (tab.state.sortKey) {
    data.sort((a, b) => {
      const vA = a[tab.state.sortKey] ?? ''
      const vB = b[tab.state.sortKey] ?? ''
      if (typeof vA === 'number' && typeof vB === 'number') {
        return tab.state.sortDir === 'asc' ? vA - vB : vB - vA
      }
      return tab.state.sortDir === 'asc' ? String(vA).localeCompare(String(vB), 'ru') : String(vB).localeCompare(String(vA), 'ru')
    })
  }
  return data
}

// Пагинация
const getPaginatedData = (tab) => {
  const filtered = getFilteredData(tab)
  const start = (tab.state.currentPage - 1) * tab.state.perPage
  return filtered.slice(start, start + tab.state.perPage)
}

const getTotalPages = (tab) => Math.ceil(getFilteredData(tab).length / tab.state.perPage)
const getDisplayedPages = (tab) => {
  const total = getTotalPages(tab)
  const current = tab.state.currentPage
  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1)
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
}

const goToPage = (tab, page) => {
  if (page >= 1 && page <= getTotalPages(tab)) tab.state.currentPage = page
}

// Обработчики
const handleSort = (tab, key) => {
  if (tab.state.sortKey === key) tab.state.sortDir = tab.state.sortDir === 'asc' ? 'desc' : 'asc'
  else { tab.state.sortKey = key; tab.state.sortDir = 'asc' }
}

const resetPage = () => tabConfig.forEach(t => t.state.currentPage = 1)
watch(() => tabConfig.map(t => JSON.stringify(t.state.filters)), resetPage)

// Форматтеры
const formatMoney = (v) => v != null ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 2 }).format(v) : '-'
const formatDate = (v) => v ? new Date(v).toLocaleDateString('ru-RU') : '-'
const getStatusBadge = (v) => {
  if (v === '0' || v === 'A' || v === 'C') return 'bg-success'
  if (v === '1' || v === 'I') return 'bg-warning text-dark'
  if (v === 'D') return 'bg-danger'
  return 'bg-secondary'
}
</script>

<style scoped>
.user-select-none { user-select: none; }
.cursor-pointer { cursor: pointer; }
.nav-link.active { font-weight: 600; color: #0d6efd !important; border-color: #dee2e6 #dee2e6 #fff !important; }
</style>
```


## Добавление возможности выгрузить результаты в Excel

Для реализации выгрузки отфильтрованных и отсортированных данных в Excel на стороне клиента (без повторных запросов к API) используйте библиотеку `xlsx` (SheetJS).

### 1. Установка зависимости

```bash
cd frontend/vue-src
npm install xlsx
```

### 2. Изменения в `frontend/vue-src/src/views/AccountsView.vue`

```vue
      <!-- ... конец формы поиска ... -->
      </form>

      <!-- 🔽 НОВАЯ ПАНЕЛЬ ЭКСПОРТА -->
      <div v-if="accounts.length" class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Показано: {{ filteredSortedAccounts.length }} из {{ accounts.length }}</span>
        <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-1" 
                @click="exportToExcel" 
                :disabled="filteredSortedAccounts.length === 0">
          📥 Экспорт в Excel
        </button>
      </div>
      <!-- 🔼 КОНЕЦ ПАНЕЛИ -->

      <!-- Таблица результатов -->
      <div v-if="accounts.length" class="table-responsive rounded border">
      <!-- ... остальной код таблицы ... -->
```

### `<script setup>` (добавьте импорт и функцию экспорта)

```js
// В начале файла добавьте импорт
import * as XLSX from 'xlsx'

// ... существующий код ...

// 🔽 НОВАЯ ФУНКЦИЯ ЭКСПОРТА
const exportToExcel = () => {
  if (filteredSortedAccounts.value.length === 0) return

  // Преобразуем отфильтрованные/отсортированные данные в плоский формат
  const exportData = filteredSortedAccounts.value.map(row => ({
    'Филиал': row.branch || '-',
    'Системный №': row.sysacc || '-',
    'Счет клиента': row.account || '-',
    'Статус': row.acc_stat || '-',
    'Дата открытия': formatDate(row.open_date),
    'Дата закрытия': formatDate(row.close_date),
    'Остаток': row.current_bal != null ? Number(row.current_bal) : 0
  }))

  // Создаем лист и книгу
  const ws = XLSX.utils.json_to_sheet(exportData)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Счета')

  // Автоширина колонок (опционально, для удобства)
  const colWidths = [
    { wch: 10 }, // Филиал
    { wch: 22 }, // Системный №
    { wch: 20 }, // Счет клиента
    { wch: 10 }, // Статус
    { wch: 12 }, // Дата открытия
    { wch: 12 }, // Дата закрытия
    { wch: 16 }  // Остаток
  ]
  ws['!cols'] = colWidths

  // Генерация имени файла и скачивание
  const fileName = `Счета_Клиент${customerNo.value}_${new Date().toISOString().slice(0, 10)}.xlsx`
  XLSX.writeFile(wb, fileName)
}
```

### Как это работает:
1. Без запросов к API: Функция берет данные напрямую из реактивного `filteredSortedAccounts`, который уже содержит результат клиентской фильтрации и сортировки.
2. Числовой формат в Excel: Остаток передается как Number, чтобы в Excel с ним можно было работать формулами (суммы, фильтры, графики). Даты передаются строками в DD.MM.YYYY.
3. Мгновенная генерация: `xlsx` работает полностью в браузере, файл формируется и скачивается за миллисекунды даже при 10k+ строк.
4. Безопасность: Кнопка автоматически блокируется (:disabled), если отфильтрованный список пуст.

## Выгрузка в Excel для блокировок, движений и ограничений

```js
import * as XLSX from 'xlsx'

// ... существующий код ...

// 🔽 ФУНКЦИЯ ЭКСПОРТА ТАБА В EXCEL
const exportTabToExcel = (tab) => {
  const data = getFilteredData(tab)
  if (!data.length) return

  // Преобразуем данные в плоский формат с русскими заголовками
  const exportRows = data.map(row => {
    const obj = {}
    tab.columns.forEach(col => {
      let val = row[col.key]
      if (col.type === 'money') obj[col.label] = val != null ? Number(val) : 0
      else if (col.type === 'date') obj[col.label] = val ? formatDate(val) : '-'
      else obj[col.label] = val || '-'
    })
    return obj
  })

  // Создаем книгу и лист
  const ws = XLSX.utils.json_to_sheet(exportRows)
  const wb = XLSX.utils.book_new()
  const sheetName = tab.label.replace(/[^\w\sа-яА-ЯёЁ]/g, '').trim()
  XLSX.utils.book_append_sheet(wb, ws, sheetName)

  // Автоширина колонок
  ws['!cols'] = tab.columns.map(col => ({ wch: Math.max(col.label.length + 2, 12) }))

  // Формируем имя файла и скачиваем
  const fileName = `${sheetName}_${search.sys_acc_no}_${new Date().toISOString().slice(0, 10)}.xlsx`
  XLSX.writeFile(wb, fileName)
}
```
