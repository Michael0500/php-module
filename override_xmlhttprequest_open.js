// Метод переопределяет ответ во всех AJAX запросах по заданому условию
(function (open) {
    XMLHttpRequest.prototype.open = function () {
        this.addEventListener("readystatechange", function (e) {
            if (4 === this.readyState && 'https://mysite.com/custom-url' === this.responseURL && "secret_data" === this.responseText) { // здесь условие когда должны быть переопределены данные ответа
                Object.defineProperty(this, 'responseText', {writable: true}); // Очень важно сделать свойство responseText изменяемым
                Object.defineProperty(this, 'respons', {writable: true}); // Очень важно сделать свойство response изменяемым
                this.responseText = '----NEW_SECRET_DATA----'; // новый ответ который должен быть возвращен
                this.response = '----NEW_SECRET_DATA----'; // новый ответ который должен быть возвращен
            }
        }, false);

        return open.apply(this, arguments);
    };
})(XMLHttpRequest.prototype.open);
