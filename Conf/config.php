<?php
return array(
    'APP_NAME' => 'ngo20map',
	'SESSION_AUTO_START'=>true,
    'URL_MODEL' => 2,
    'URL_HTML_SUFFIX'=>'',
    
    "LOAD_EXT_FILE"=>"htmlhelpers,auth,qndmodel",
    'LOAD_EXT_CONFIG' => 'db,content,credentials,mail_templates',
    'VAR_PAGE' => 'p',
    'MD5_SALT' => 'flwei^e417',
    'TMPL_STRIP_SPACE' => false,
    
    'ADMIN_ROW_LIST' => 20,
    'PAGE_ROLLPAGE' => 10,
    'LIST_RECORD_PER_PAGE' => 20,
    'RECORD_PER_POST_WIDGET' => 5,
    'VAR_PAGE' => 'p',
    'LOG_RECORD' => true,
    'LOG_LEVEL' => 'EMERG,ALERT,CRIT,ERR,INFO,SQL',
    
);
?>