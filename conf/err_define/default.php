<?php
return [
    'OK' => ['code' => 200, 'message' => 'OK'],
    'API_NOT_EXISTS' => ['code' => 404, 'message' => 'API "{{:api}}" 不存在'],
    'HTTP_METHOD_ERROR' => ['code' => 500, 'message' => '不支持的 HTTP METHOD'],
    'PARAM_ERROR' => ['code' => 400, 'message' => '参数错误'],
    'API_PATH_ERROR' => ['code' => 500, 'message' => 'API 路径错误'],
    'PARAM_MUST_IS_NUMBER' => ['code' => '10004', 'message' => '参数 "{{:param}}" 必须是数字'],
    'PARAM_MUST_NOT_MORE_THAN' => ['code' => '10004', 'message' => '参数 "{{:param}}" 不得大于 {{:max}}'],
    'PARAM_MUST_NOT_LITTLE_THAN' => ['code' => '10004', 'message' => '参数 "{{:param}}" 不得小于 {{:min}}'],
    'PARAM_MUST_NOT_LONG_THAN' => ['code' => '10003', 'message' => '参数 "{{:param}}" 不得长于 {{:max}}'],
    'PARAM_MUST_NOT_SHORT_THAN' => ['code' => '10003', 'message' => '参数 "{{:param}}" 不得短于 {{:min}}'],
    'PARAM_MUST_NOT_EMPTY' => ['code' => '10002', 'message' => '参数 "{{:param}}" 不得为空'],
    'PARAM_MUST_EXISTS' => ['code' => '10001', 'message' => '参数 "{{:param}}" 必须存在'],
];
