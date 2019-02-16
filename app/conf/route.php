<?php

$dispatcher = \FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '', 'Index::index');
    // {id} must be a number (\d+)
    $r->addRoute('GET', '/user/{id:\d+}', 'Index::index');
    // The /{title} suffix is optional
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

return $dispatcher;
