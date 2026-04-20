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

    Кэширование Pinia: При первом запросе данные сохраняются под ключом accounts:<номер>. При повторных переходах на страницу или изменении фильтров/сортировки API не вызывается.
    Клиентская сортировка и фильтрация: Работает мгновенно через computed, не нагружает сервер. Фильтры по каждому столбцу, сортировка по клику на заголовок.
    Валидация: Поле принимает только цифры, блокирует отправку, если введено не 8 символов.
    UI/UX: Используются компоненты Bootstrap (card, table, badge, spinner). Глобальный лоадер (<AppLoader />) автоматически появляется при запросе API, кнопка "Найти" блокируется во избежание дублирования запросов.
    Форматирование: Даты приводятся к ru-RU формату, суммы отображаются как валюта с разделителями.
