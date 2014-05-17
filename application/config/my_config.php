<?php
$config['my_session']['save_path'] = 'D:\wamp\www\innterface\innterface_lab\tmp';
$config['my_session']['name'] = 'innterface_lab_sess';

$config['my_session']['gc_probability'] = 1;
$config['my_session']['gc_divisor'] = 100;
$config['my_session']['gc_maxlifetime'] =  7200;

$config['my_session']['cookie_lifetime'] = 60*60*12;
$config['my_session']['cookie_path'] = '/';
$config['my_session']['cookie_domain'] = '';

$config['raw_data']['itunes_rss']['dir'] = 'D:\wamp\www\innterface\innterface_lab\raw_data\itunes_rss';
$config['raw_data']['search_api']['dir'] = 'D:\wamp\www\innterface\innterface_lab\raw_data\search_api';

$config['upload_tmp'] = 'webdata/tmp';
$config['upload_file'] = 'webdata/upload_file';
$config['file_ext'] = array('jpg'=>'jpg',
                            'jpeg'=>'jpg',
                            'gif'=>'gif',
                            'png'=>'png',
                            );
$config['usage_pass'] = 'EiA4y4C7';
$config['synonym_mng_pass'] = 'KZSVM5H3';
?>