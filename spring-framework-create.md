# Настройка Spring

## Создание проекта

```shell
mvn archetype:generate -DgroupId=ru.chitushka -DartifactId=my-java-app -DarchetypeArtifactId=maven-archetype-quickstart -DarchetypeVersion=1.4 -DinteractiveMode=false
```

## Простейший xml для конфигурации бинов:

Сохраняем файл конфигураций в папке `resources` с именем `applicationContext.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<beans xmlns="http://www.springframework.org/schema/beans"
       xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:schemaLocation="http://www.springframework.org/schema/beans
        https://www.springframework.org/schema/beans/spring-beans.xsd">

    <bean id="..." class="...">  
        <!-- collaborators and configuration for this bean go here -->
    </bean>

</beans>
```

Затем для его использования пишем
```java
ApplicationContext context = new ClassPathXmlApplicationContext("applicationContext.xml");
```

Если у нас есть несколько файлов конфигов, мы можем их все объеденить в один таким образом

```xml
<beans>
    <import resource="services.xml"/>
    <import resource="resources/messageSource.xml"/>
    <import resource="/resources/themeSource.xml"/>

    <bean id="bean1" class="..."/>
    <bean id="bean2" class="..."/>
</beans>
```

и использовать его вместо передачи этих все файлов в аргументе конструктора контекста `ClassPathXmlApplicationContext` конфиг файлов 

```java
ApplicationContext context = new ClassPathXmlApplicationContext("applicationContext.xml", "services.xml", "messageSource.xml");
```

из данного контекста мы можем получать объекты (бины) через метод `getBean()`

Существуют различные классы контекста приложения `ApplicationContext, ClassPathXmlApplicationContext, GenericGroovyApplicationContext, GenericApplicationContext`

```java
GenericApplicationContext context = new GenericApplicationContext();
new XmlBeanDefinitionReader(context).loadBeanDefinitions("services.xml", "daos.xml");
context.refresh();
```
