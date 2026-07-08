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
  <title>Управление</title>
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