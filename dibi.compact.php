<?php //netteloader=IDibiVariable,IDataSource,IDibiProfiler,IDibiDriver,DibiObject,DibiException,DibiDriverException,DibiConnection,DibiResult,DibiRow,DibiTranslator,DibiVariable,DibiTableX,DibiTable,DibiDataSource,DibiFluent,DibiDatabaseInfo,DibiTableInfo,DibiColumnInfo,DibiForeignKeyInfo,DibiIndexInfo,DibiProfiler,dibi,DibiMsSqlDriver,DibiMySqlDriver,DibiMySqliDriver,DibiOdbcDriver,DibiOracleDriver,DibiPdoDriver,DibiPostgreDriver,DibiSqliteDriver

/**
 * dibi - tiny'n'smart database abstraction layer
 * ----------------------------------------------
 *
 * Copyright (c) 2005, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "dibi license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://dibiphp.com
 *
 * @copyright  Copyright (c) 2005, 2008 David Grudl
 * @license    http://dibiphp.com/license  dibi license
 * @link       http://dibiphp.com
 * @package    dibi
 * @version    1.0 (revision 166 released on 2008/10/30 16:40:17)
 */

if(version_compare(PHP_VERSION,'5.1.0','<')){throw
new
Exception('dibi needs PHP 5.1.0 or newer.');}if(!class_exists('NotImplementedException',FALSE)){class
NotImplementedException
extends
LogicException{}}if(!class_exists('NotSupportedException',FALSE)){class
NotSupportedException
extends
LogicException{}}if(!class_exists('MemberAccessException',FALSE)){class
MemberAccessException
extends
LogicException{}}if(!class_exists('InvalidStateException',FALSE)){class
InvalidStateException
extends
RuntimeException{}}if(!class_exists('IOException',FALSE)){class
IOException
extends
RuntimeException{}}if(!class_exists('FileNotFoundException',FALSE)){class
FileNotFoundException
extends
IOException{}}if(!interface_exists('IDebuggable',FALSE)){interface
IDebuggable{function
getPanels();}}interface
IDibiVariable{function
toSql(DibiTranslator$translator,$modifier);}interface
IDataSource
extends
Countable,IteratorAggregate{}interface
IDibiProfiler{const
CONNECT=1;const
SELECT=4;const
INSERT=8;const
DELETE=16;const
UPDATE=32;const
QUERY=60;const
BEGIN=64;const
COMMIT=128;const
ROLLBACK=256;const
TRANSACTION=448;const
EXCEPTION=512;const
ALL=1023;function
before(DibiConnection$connection,$event,$sql=NULL);function
after($ticket,$result=NULL);function
exception(DibiDriverException$exception);}interface
IDibiDriver{function
connect(array&$config);function
disconnect();function
query($sql);function
affectedRows();function
insertId($sequence);function
begin();function
commit();function
rollback();function
getResource();function
escape($value,$type);function
unescape($value,$type);function
applyLimit(&$sql,$limit,$offset);function
rowCount();function
seek($row);function
fetch($type);function
free();function
getColumnsMeta();function
getResultResource();function
getTables();function
getColumns($table);function
getIndexes($table);function
getForeignKeys($table);}abstract
class
DibiObject{private
static$extMethods;final
public
function
getClass(){return
get_class($this);}final
public
function
getReflection(){return
new
ReflectionObject($this);}public
function
__call($name,$args){$class=get_class($this);if($name===''){throw
new
MemberAccessException("Call to class '$class' method without name.");}if(preg_match('#^on[A-Z]#',$name)){$rp=new
ReflectionProperty($class,$name);if($rp->isPublic()&&!$rp->isStatic()){$list=$this->$name;if(is_array($list)||$list
instanceof
Traversable){foreach($list
as$handler){if(is_object($handler)){call_user_func_array(array($handler,'__invoke'),$args);}else{call_user_func_array($handler,$args);}}}return
NULL;}}if($cb=self::extensionMethod("$class::$name")){array_unshift($args,$this);return
call_user_func_array($cb,$args);}throw
new
MemberAccessException("Call to undefined method $class::$name().");}public
static
function
__callStatic($name,$args){$class=get_called_class();throw
new
MemberAccessException("Call to undefined static method $class::$name().");}public
static
function
extensionMethod($name,$callback=NULL){if(self::$extMethods===NULL||$name===NULL){$list=get_defined_functions();foreach($list['user']as$fce){$pair=explode('_prototype_',$fce);if(count($pair)===2){self::$extMethods[$pair[1]][$pair[0]]=$fce;self::$extMethods[$pair[1]]['']=NULL;}}if($name===NULL)return
NULL;}$name=strtolower($name);$a=strrpos($name,':');if($a===FALSE){$class=strtolower(get_called_class());$l=&self::$extMethods[$name];}else{$class=substr($name,0,$a-1);$l=&self::$extMethods[substr($name,$a+1)];}if($callback!==NULL){$l[$class]=$callback;$l['']=NULL;return
NULL;}if(empty($l)){return
FALSE;}elseif(isset($l[''][$class])){return$l[''][$class];}$cl=$class;do{$cl=strtolower($cl);if(isset($l[$cl])){return$l[''][$class]=$l[$cl];}}while(($cl=get_parent_class($cl))!==FALSE);foreach(class_implements($class)as$cl){$cl=strtolower($cl);if(isset($l[$cl])){return$l[''][$class]=$l[$cl];}}return$l[''][$class]=FALSE;}public
function&__get($name){$class=get_class($this);if($name===''){throw
new
MemberAccessException("Cannot read an class '$class' property without name.");}$name[0]=$name[0]&"\xDF";$m='get'.$name;if(self::hasAccessor($class,$m)){$val=$this->$m();return$val;}$m='is'.$name;if(self::hasAccessor($class,$m)){$val=$this->$m();return$val;}$name=func_get_arg(0);throw
new
MemberAccessException("Cannot read an undeclared property $class::\$$name.");}public
function
__set($name,$value){$class=get_class($this);if($name===''){throw
new
MemberAccessException("Cannot assign to an class '$class' property without name.");}$name[0]=$name[0]&"\xDF";if(self::hasAccessor($class,'get'.$name)||self::hasAccessor($class,'is'.$name)){$m='set'.$name;if(self::hasAccessor($class,$m)){$this->$m($value);return;}else{$name=func_get_arg(0);throw
new
MemberAccessException("Cannot assign to a read-only property $class::\$$name.");}}$name=func_get_arg(0);throw
new
MemberAccessException("Cannot assign to an undeclared property $class::\$$name.");}public
function
__isset($name){$name[0]=$name[0]&"\xDF";return$name!==''&&self::hasAccessor(get_class($this),'get'.$name);}public
function
__unset($name){$class=get_class($this);throw
new
MemberAccessException("Cannot unset an property $class::\$$name.");}private
static
function
hasAccessor($c,$m){static$cache;if(!isset($cache[$c])){$cache[$c]=array_flip(get_class_methods($c));}return
isset($cache[$c][$m]);}}class
DibiException
extends
Exception{}class
DibiDriverException
extends
DibiException
implements
IDebuggable{private
static$errorMsg;private$sql;public
function
__construct($message=NULL,$code=0,$sql=NULL){parent::__construct($message,(int)$code);$this->sql=$sql;}final
public
function
getSql(){return$this->sql;}public
function
__toString(){return
parent::__toString().($this->sql?"\nSQL: ".$this->sql:'');}public
function
getPanels(){$panels=array();if($this->sql!==NULL){$panels['SQL']=array('expanded'=>TRUE,'content'=>dibi::dump($this->sql,TRUE));}return$panels;}public
static
function
tryError(){set_error_handler(array(__CLASS__,'_errorHandler'),E_ALL);self::$errorMsg=NULL;}public
static
function
catchError(&$message){restore_error_handler();$message=self::$errorMsg;self::$errorMsg=NULL;return$message!==NULL;}public
static
function
_errorHandler($code,$message){restore_error_handler();if(ini_get('html_errors')){$message=strip_tags($message);$message=html_entity_decode($message);}self::$errorMsg=$message;}}class
DibiConnection
extends
DibiObject{private$config;private$driver;private$profiler;private$connected=FALSE;private$inTxn=FALSE;public
function
__construct($config,$name=NULL){if(class_exists('Debug',FALSE)){Debug::addColophon(array('dibi','getColophon'));}if(is_string($config)){parse_str($config,$config);}elseif($config
instanceof
ArrayObject){$config=(array)$config;}elseif(!is_array($config)){throw
new
InvalidArgumentException('Configuration must be array, string or ArrayObject.');}if(!isset($config['driver'])){$config['driver']=dibi::$defaultDriver;}$driver=preg_replace('#[^a-z0-9_]#','_',$config['driver']);$class="Dibi".$driver."Driver";if(!class_exists($class,FALSE)){ include_once dirname(__FILE__)."/../drivers/$driver.php";if(!class_exists($class,FALSE)){throw
new
DibiException("Unable to create instance of dibi driver '$class'.");}}$config['name']=$name;$this->config=$config;$this->driver=new$class;if(!empty($config['profiler'])){$class=$config['profiler'];if(is_numeric($class)||is_bool($class)){$class='DibiProfiler';}if(!class_exists($class)){throw
new
DibiException("Unable to create instance of dibi profiler '$class'.");}$this->setProfiler(new$class);}if(empty($config['lazy'])){$this->connect();}}public
function
__destruct(){$this->disconnect();}final
protected
function
connect(){if(!$this->connected){if($this->profiler!==NULL){$ticket=$this->profiler->before($this,IDibiProfiler::CONNECT);}$this->driver->connect($this->config);$this->connected=TRUE;if(isset($ticket)){$this->profiler->after($ticket);}}}final
public
function
disconnect(){if($this->connected){if($this->inTxn){$this->rollback();}$this->driver->disconnect();$this->connected=FALSE;}}final
public
function
isConnected(){return$this->connected;}final
public
function
getConfig($key=NULL,$default=NULL){if($key===NULL){return$this->config;}elseif(isset($this->config[$key])){return$this->config[$key];}else{return$default;}}public
static
function
alias(&$config,$key,$alias=NULL){if(isset($config[$key]))return;if($alias!==NULL&&isset($config[$alias])){$config[$key]=$config[$alias];unset($config[$alias]);}else{$config[$key]=NULL;}}final
public
function
getResource(){return$this->driver->getResource();}final
public
function
query($args){$args=func_get_args();$this->connect();$trans=new
DibiTranslator($this->driver);if($trans->translate($args)){return$this->nativeQuery($trans->sql);}else{throw
new
DibiException('SQL translate error: '.$trans->sql);}}final
public
function
test($args){$args=func_get_args();$this->connect();$trans=new
DibiTranslator($this->driver);$ok=$trans->translate($args);dibi::dump($trans->sql);return$ok;}final
public
function
nativeQuery($sql){$this->connect();if($this->profiler!==NULL){$event=IDibiProfiler::QUERY;if(preg_match('#\s*(SELECT|UPDATE|INSERT|DELETE)#i',$sql,$matches)){static$events=array('SELECT'=>IDibiProfiler::SELECT,'UPDATE'=>IDibiProfiler::UPDATE,'INSERT'=>IDibiProfiler::INSERT,'DELETE'=>IDibiProfiler::DELETE);$event=$events[strtoupper($matches[1])];}$ticket=$this->profiler->before($this,$event,$sql);}dibi::$numOfQueries++;dibi::$sql=$sql;dibi::$elapsedTime=FALSE;$time=-microtime(TRUE);if($res=$this->driver->query($sql)){$res=new
DibiResult($res,$this->config);}$time+=microtime(TRUE);dibi::$elapsedTime=$time;dibi::$totalTime+=$time;if(isset($ticket)){$this->profiler->after($ticket,$res);}return$res;}public
function
affectedRows(){$rows=$this->driver->affectedRows();if(!is_int($rows)||$rows<0)throw
new
DibiException('Cannot retrieve number of affected rows.');return$rows;}public
function
insertId($sequence=NULL){$id=$this->driver->insertId($sequence);if($id<1)throw
new
DibiException('Cannot retrieve last generated ID.');return(int)$id;}public
function
begin(){$this->connect();if($this->inTxn){throw
new
DibiException('There is already an active transaction.');}if($this->profiler!==NULL){$ticket=$this->profiler->before($this,IDibiProfiler::BEGIN);}$this->driver->begin();$this->inTxn=TRUE;if(isset($ticket)){$this->profiler->after($ticket);}}public
function
commit(){if(!$this->inTxn){throw
new
DibiException('There is no active transaction.');}if($this->profiler!==NULL){$ticket=$this->profiler->before($this,IDibiProfiler::COMMIT);}$this->driver->commit();$this->inTxn=FALSE;if(isset($ticket)){$this->profiler->after($ticket);}}public
function
rollback(){if(!$this->inTxn){throw
new
DibiException('There is no active transaction.');}if($this->profiler!==NULL){$ticket=$this->profiler->before($this,IDibiProfiler::ROLLBACK);}$this->driver->rollback();$this->inTxn=FALSE;if(isset($ticket)){$this->profiler->after($ticket);}}public
function
escape($value,$type=dibi::FIELD_TEXT){$this->connect();return$this->driver->escape($value,$type);}public
function
unescape($value,$type=dibi::FIELD_BINARY){return$this->driver->unescape($value,$type);}public
function
delimite($value){return$this->driver->escape($value,dibi::IDENTIFIER);}public
function
applyLimit(&$sql,$limit,$offset){$this->driver->applyLimit($sql,$limit,$offset);}public
function
command(){return
new
DibiFluent($this);}public
function
select($args){$args=func_get_args();return$this->command()->__call('select',$args);}public
function
update($table,array$args){return$this->command()->update('%n',$table)->set($args);}public
function
insert($table,array$args){return$this->command()->insert()->into('%n',$table,'(%n)',array_keys($args))->values('%l',array_values($args));}public
function
delete($table){return$this->command()->delete()->from('%n',$table);}public
function
setProfiler(IDibiProfiler$profiler=NULL){$this->profiler=$profiler;}public
function
getProfiler(){return$this->profiler;}public
function
loadFile($file){$this->connect();@set_time_limit(0);$handle=@fopen($file,'r');if(!$handle){throw
new
FileNotFoundException("Cannot open file '$file'.");}$count=0;$sql='';while(!feof($handle)){$s=fgets($handle);$sql.=$s;if(substr(rtrim($s),-1)===';'){$this->driver->query($sql);$sql='';$count++;}}fclose($handle);return$count;}public
function
getDatabaseInfo(){return
new
DibiDatabaseInfo($this->driver,isset($this->config['database'])?$this->config['database']:NULL);}public
function
__wakeup(){throw
new
NotSupportedException('You cannot serialize or unserialize '.$this->getClass().' instances.');}public
function
__sleep(){throw
new
NotSupportedException('You cannot serialize or unserialize '.$this->getClass().' instances.');}}class
DibiResult
extends
DibiObject
implements
IDataSource{private$driver;private$xlat;private$meta;private$fetched=FALSE;private$withTables=FALSE;private$class='DibiRow';public
function
__construct($driver,$config){$this->driver=$driver;if(!empty($config[dibi::RESULT_WITH_TABLES])){$this->setWithTables(TRUE);}}public
function
__destruct(){@$this->free();}final
public
function
getResource(){return$this->getDriver()->getResultResource();}final
public
function
seek($row){return($row!==0||$this->fetched)?(bool)$this->getDriver()->seek($row):TRUE;}final
public
function
rowCount(){return$this->getDriver()->rowCount();}final
public
function
free(){if($this->driver!==NULL){$this->driver->free();$this->driver=NULL;}}final
public
function
setWithTables($val){if($val){$cols=array();foreach($this->getMeta()as$info){$name=$info['fullname'];if(isset($cols[$name])){$fix=1;while(isset($cols[$name.'#'.$fix]))$fix++;$name.='#'.$fix;}$cols[$name]=TRUE;}$this->withTables=array_keys($cols);}else{$this->withTables=FALSE;}}final
public
function
getWithTables(){return(bool)$this->withTables;}public
function
setRowClass($class){$this->class=$class;}public
function
getRowClass(){return$this->class;}final
public
function
fetch(){if($this->withTables===FALSE){$row=$this->getDriver()->fetch(TRUE);if(!is_array($row))return
FALSE;}else{$row=$this->getDriver()->fetch(FALSE);if(!is_array($row))return
FALSE;$row=array_combine($this->withTables,$row);}$this->fetched=TRUE;if($this->xlat!==NULL){foreach($this->xlat
as$col=>$type){if(isset($row[$col])){$row[$col]=$this->convert($row[$col],$type['type'],$type['format']);}}}return
new$this->class($row);}final
public
function
fetchSingle(){$row=$this->getDriver()->fetch(TRUE);if(!is_array($row))return
FALSE;$this->fetched=TRUE;$value=reset($row);$key=key($row);if(isset($this->xlat[$key])){$type=$this->xlat[$key];return$this->convert($value,$type['type'],$type['format']);}return$value;}final
public
function
fetchAll($offset=NULL,$limit=NULL){$limit=$limit===NULL?-1:(int)$limit;$this->seek((int)$offset);$row=$this->fetch();if(!$row)return
array();$data=array();do{if($limit===0)break;$limit--;$data[]=$row;}while($row=$this->fetch());return$data;}final
public
function
fetchAssoc($assoc){$this->seek(0);$row=$this->fetch();if(!$row)return
array();$data=NULL;$assoc=explode(',',$assoc);foreach($assoc
as$as){if($as!=='#'&&$as!=='='&&$as!=='@'&&!isset($row[$as])){throw
new
InvalidArgumentException("Unknown column '$as' in associative descriptor.");}}$leaf='@';$last=count($assoc)-1;while($assoc[$last]==='='||$assoc[$last]==='@'){$leaf=$assoc[$last];unset($assoc[$last]);$last--;if($last<0){$assoc[]='#';break;}}do{$arr=(array)$row;$x=&$data;foreach($assoc
as$i=>$as){if($as==='#'){$x=&$x[];}elseif($as==='='){if($x===NULL){$x=$arr;$x=&$x[$assoc[$i+1]];$x=NULL;}else{$x=&$x[$assoc[$i+1]];}}elseif($as==='@'){if($x===NULL){$x=clone$row;$x=&$x->{$assoc[$i+1]};$x=NULL;}else{$x=&$x->{$assoc[$i+1]};}}else{$x=&$x[$arr[$as]];}}if($x===NULL){if($leaf==='='){$x=$arr;}else{$x=$row;}}}while($row=$this->fetch());unset($x);return$data;}final
public
function
fetchPairs($key=NULL,$value=NULL){$this->seek(0);$row=$this->fetch();if(!$row)return
array();$data=array();if($value===NULL){if($key!==NULL){throw
new
InvalidArgumentException("Either none or both columns must be specified.");}$tmp=array_keys((array)$row);$key=$tmp[0];if(count($row)<2){do{$data[]=$row[$key];}while($row=$this->fetch());return$data;}$value=$tmp[1];}else{if(!isset($row[$value])){throw
new
InvalidArgumentException("Unknown value column '$value'.");}if($key===NULL){do{$data[]=$row[$value];}while($row=$this->fetch());return$data;}if(!isset($row[$key])){throw
new
InvalidArgumentException("Unknown key column '$key'.");}}do{$data[$row[$key]]=$row[$value];}while($row=$this->fetch());return$data;}final
public
function
setType($col,$type,$format=NULL){$this->xlat[$col]=array('type'=>$type,'format'=>$format);}final
public
function
detectTypes(){foreach($this->getMeta()as$info){$this->xlat[$info['name']]=array('type'=>$info['type'],'format'=>NULL);}}final
public
function
setTypes(array$types){$this->xlat=$types;}final
public
function
getType($col){return
isset($this->xlat[$col])?$this->xlat[$col]:NULL;}final
public
function
convert($value,$type,$format=NULL){if($value===NULL||$value===FALSE){return$value;}switch($type){case
dibi::FIELD_TEXT:return(string)$value;case
dibi::FIELD_BINARY:return$this->getDriver()->unescape($value,$type);case
dibi::FIELD_INTEGER:return(int)$value;case
dibi::FIELD_FLOAT:return(float)$value;case
dibi::FIELD_DATE:case
dibi::FIELD_DATETIME:$value=strtotime($value);return$format===NULL?$value:date($format,$value);case
dibi::FIELD_BOOL:return((bool)$value)&&$value!=='f'&&$value!=='F';default:return$value;}}final
public
function
getColumns(){$cols=array();foreach($this->getMeta()as$info){$cols[]=new
DibiColumnInfo($this->driver,$info);}return$cols;}public
function
getColumnNames($withTables=FALSE){$cols=array();foreach($this->getMeta()as$info){$cols[]=$info[$withTables?'fullname':'name'];}return$cols;}final
public
function
dump(){$i=0;$this->seek(0);while($row=$this->fetch()){if($i===0){echo"\n<table class=\"dump\">\n<thead>\n\t<tr>\n\t\t<th>#row</th>\n";foreach($row
as$col=>$foo){echo"\t\t<th>".htmlSpecialChars($col)."</th>\n";}echo"\t</tr>\n</thead>\n<tbody>\n";}echo"\t<tr>\n\t\t<th>",$i,"</th>\n";foreach($row
as$col){echo"\t\t<td>",htmlSpecialChars($col),"</td>\n";}echo"\t</tr>\n";$i++;}if($i===0){echo'<p><em>empty result set</em></p>';}else{echo"</tbody>\n</table>\n";}}final
public
function
getIterator($offset=NULL,$limit=NULL){return
new
ArrayIterator($this->fetchAll($offset,$limit));}final
public
function
count(){return$this->rowCount();}private
function
getDriver(){if($this->driver===NULL){throw
new
InvalidStateException('Resultset was released from memory.');}return$this->driver;}private
function
getMeta(){if($this->meta===NULL){$this->meta=$this->getDriver()->getColumnsMeta();foreach($this->meta
as&$row){$row['type']=DibiColumnInfo::detectType($row['nativetype']);}}return$this->meta;}}class
DibiRow
extends
ArrayObject{public
function
__construct($arr){parent::__construct($arr,2);}}final
class
DibiTranslator
extends
DibiObject{public$sql;private$driver;private$cursor;private$args;private$hasError;private$comment;private$ifLevel;private$ifLevelStart;private$limit;private$offset;public
function
__construct(IDibiDriver$driver){$this->driver=$driver;}public
function
getDriver(){return$this->driver;}public
function
translate(array$args){$this->limit=-1;$this->offset=0;$this->hasError=FALSE;$commandIns=NULL;$lastArr=NULL;$cursor=&$this->cursor;$cursor=0;$this->args=array_values($args);$args=&$this->args;$this->ifLevel=$this->ifLevelStart=0;$comment=&$this->comment;$comment=FALSE;$sql=array();while($cursor<count($args)){$arg=$args[$cursor];$cursor++;if(is_string($arg)){$toSkip=strcspn($arg,'`[\'"%');if(strlen($arg)===$toSkip){$sql[]=$arg;}else{$sql[]=substr($arg,0,$toSkip).preg_replace_callback('/(?=`|\[|\'|"|%)(?:`(.+?)`|\[(.+?)\]|(\')((?:\'\'|[^\'])*)\'|(")((?:""|[^"])*)"|(\'|")|%([a-zA-Z]{1,4})(?![a-zA-Z]))/s',array($this,'cb'),substr($arg,$toSkip));}continue;}if($comment){$sql[]='...';continue;}if(is_array($arg)){if(is_string(key($arg))){if($commandIns===NULL){$commandIns=strtoupper(substr(ltrim($args[0]),0,6));$commandIns=$commandIns==='INSERT'||$commandIns==='REPLAC';$sql[]=$this->formatValue($arg,$commandIns?'v':'a');}else{if($lastArr===$cursor-1)$sql[]=',';$sql[]=$this->formatValue($arg,$commandIns?'l':'a');}$lastArr=$cursor;continue;}elseif($cursor===1){$cursor=0;array_splice($args,0,1,$arg);continue;}}$sql[]=$this->formatValue($arg,FALSE);}if($comment)$sql[]="*/";$sql=implode(' ',$sql);if($this->limit>-1||$this->offset>0){$this->driver->applyLimit($sql,$this->limit,$this->offset);}$this->sql=$sql;return!$this->hasError;}public
function
formatValue($value,$modifier){if(is_array($value)||$value
instanceof
ArrayObject){$vx=$kx=array();$operator=', ';switch($modifier){case'and':case'or':$operator=' '.strtoupper($modifier).' ';if(empty($value)){return'1';}elseif(!is_string(key($value))){foreach($value
as$v){$vx[]=$this->formatValue($v,'sql');}}else{foreach($value
as$k=>$v){$pair=explode('%',$k,2);$k=$this->delimite($pair[0]);$v=$this->formatValue($v,isset($pair[1])?$pair[1]:FALSE);$op=isset($pair[1])&&$pair[1]==='l'?'IN':($v==='NULL'?'IS':'=');$vx[]=$k.' '.$op.' '.$v;}}return
implode($operator,$vx);case'a':foreach($value
as$k=>$v){$pair=explode('%',$k,2);$vx[]=$this->delimite($pair[0]).'='.$this->formatValue($v,isset($pair[1])?$pair[1]:FALSE);}return
implode($operator,$vx);case'l':foreach($value
as$k=>$v){$pair=explode('%',$k,2);$vx[]=$this->formatValue($v,isset($pair[1])?$pair[1]:FALSE);}return'('.implode(', ',$vx).')';case'v':foreach($value
as$k=>$v){$pair=explode('%',$k,2);$kx[]=$this->delimite($pair[0]);$vx[]=$this->formatValue($v,isset($pair[1])?$pair[1]:FALSE);}return'('.implode(', ',$kx).') VALUES ('.implode(', ',$vx).')';case'by':foreach($value
as$k=>$v){if(is_string($k)){$v=(is_string($v)&&strncasecmp($v,'d',1))||$v>0?'ASC':'DESC';$vx[]=$this->delimite($k).' '.$v;}else{$vx[]=$this->delimite($v);}}return
implode(', ',$vx);default:foreach($value
as$v){$vx[]=$this->formatValue($v,$modifier);}return
implode(', ',$vx);}}if($modifier){if($value===NULL){return'NULL';}if($value
instanceof
IDibiVariable){return$value->toSql($this,$modifier);}if(!is_scalar($value)){$this->hasError=TRUE;return'**Unexpected type '.gettype($value).'**';}switch($modifier){case's':case'bin':case'b':return$this->driver->escape($value,$modifier);case'sn':return$value==''?'NULL':$this->driver->escape($value,dibi::FIELD_TEXT);case'i':case'u':if(is_string($value)&&preg_match('#[+-]?\d+(e\d+)?$#A',$value)){return$value;}return(string)(int)($value+0);case'f':if(is_string($value)&&is_numeric($value)&&strpos($value,'x')===FALSE){return$value;}return
rtrim(rtrim(number_format($value,5,'.',''),'0'),'.');case'd':case't':return$this->driver->escape(is_string($value)?strtotime($value):$value,$modifier);case'by':case'n':return$this->delimite($value);case'sql':$value=(string)$value;$toSkip=strcspn($value,'`[\'"');if(strlen($value)===$toSkip){return$value;}else{return
substr($value,0,$toSkip).preg_replace_callback('/(?=`|\[|\'|")(?:`(.+?)`|\[(.+?)\]|(\')((?:\'\'|[^\'])*)\'|(")((?:""|[^"])*)"(\'|"))/s',array($this,'cb'),substr($value,$toSkip));}case'and':case'or':case'a':case'l':case'v':$this->hasError=TRUE;return'**Unexpected type '.gettype($value).'**';default:$this->hasError=TRUE;return"**Unknown or invalid modifier %$modifier**";}}if(is_string($value))return$this->driver->escape($value,dibi::FIELD_TEXT);if(is_int($value)||is_float($value))return
rtrim(rtrim(number_format($value,5,'.',''),'0'),'.');if(is_bool($value))return$this->driver->escape($value,dibi::FIELD_BOOL);if($value===NULL)return'NULL';if($value
instanceof
IDibiVariable)return$value->toSql($this,NULL);$this->hasError=TRUE;return'**Unexpected '.gettype($value).'**';}private
function
cb($matches){if(!empty($matches[8])){$mod=$matches[8];$cursor=&$this->cursor;if($cursor>=count($this->args)&&$mod!=='else'&&$mod!=='end'){$this->hasError=TRUE;return"**Extra modifier %$mod**";}if($mod==='if'){$this->ifLevel++;$cursor++;if(!$this->comment&&!$this->args[$cursor-1]){$this->ifLevelStart=$this->ifLevel;$this->comment=TRUE;return"/*";}return'';}elseif($mod==='else'){if($this->ifLevelStart===$this->ifLevel){$this->ifLevelStart=0;$this->comment=FALSE;return"*/";}elseif(!$this->comment){$this->ifLevelStart=$this->ifLevel;$this->comment=TRUE;return"/*";}}elseif($mod==='end'){$this->ifLevel--;if($this->ifLevelStart===$this->ifLevel+1){$this->ifLevelStart=0;$this->comment=FALSE;return"*/";}return'';}elseif($mod==='ex'){array_splice($this->args,$cursor,1,$this->args[$cursor]);return'';}elseif($mod==='lmt'){if($this->args[$cursor]!==NULL)$this->limit=(int)$this->args[$cursor];$cursor++;return'';}elseif($mod==='ofs'){if($this->args[$cursor]!==NULL)$this->offset=(int)$this->args[$cursor];$cursor++;return'';}else{$cursor++;return$this->formatValue($this->args[$cursor-1],$mod);}}if($this->comment)return'...';if($matches[1])return$this->delimite($matches[1]);if($matches[2])return$this->delimite($matches[2]);if($matches[3])return$this->driver->escape(str_replace("''","'",$matches[4]),dibi::FIELD_TEXT);if($matches[5])return$this->driver->escape(str_replace('""','"',$matches[6]),dibi::FIELD_TEXT);if($matches[7]){$this->hasError=TRUE;return'**Alone quote**';}die('this should be never executed');}private
function
delimite($value){return$this->driver->escape(dibi::substitute($value),dibi::IDENTIFIER);}}class
DibiVariable
extends
DibiObject
implements
IDibiVariable{public$value;public$modifier;public
function
__construct($value,$modifier){$this->value=$value;$this->modifier=$modifier;}public
function
toSql(DibiTranslator$translator,$modifier){return$translator->formatValue($this->value,$this->modifier);}}abstract
class
DibiTableX
extends
DibiObject{public
static$primaryMask='id';public
static$lowerCase=TRUE;private$connection;protected$name;protected$primary;protected$primaryModifier='%i';protected$primaryAutoIncrement=TRUE;protected$blankRow=array();protected$types=array();public
function
__construct(DibiConnection$connection=NULL){$this->connection=$connection===NULL?dibi::getConnection():$connection;$this->setup();}public
function
getName(){return$this->name;}public
function
getPrimary(){return$this->primary;}public
function
getConnection(){return$this->connection;}protected
function
setup(){if($this->name===NULL){$name=$this->getClass();if(FALSE!==($pos=strrpos($name,':'))){$name=substr($name,$pos+1);}if(self::$lowerCase){$name=strtolower($name);}$this->name=$name;}if($this->primary===NULL){$this->primary=str_replace(array('%p','%s'),array($this->name,trim($this->name,'s')),self::$primaryMask);}}public
function
insert($data){$this->connection->query('INSERT INTO %n',$this->name,'%v',$this->prepare($data));return$this->primaryAutoIncrement?$this->connection->insertId():NULL;}public
function
update($where,$data){$data=$this->prepare($data);if($where===NULL&&isset($data[$this->primary])){;$where=$data[$this->primary];unset($data[$this->primary]);}$this->connection->query('UPDATE %n',$this->name,'SET %a',$data,'WHERE %n',$this->primary,'IN ('.$this->primaryModifier,$where,')');return$this->connection->affectedRows();}public
function
insertOrUpdate($data){$data=$this->prepare($data);if(!isset($data[$this->primary])){throw
new
InvalidArgumentException("Missing primary key '$this->primary' in dataset.");}try{$this->connection->query('INSERT INTO %n',$this->name,'%v',$data);}catch(DibiDriverException$e){$where=$data[$this->primary];unset($data[$this->primary]);$this->connection->query('UPDATE %n',$this->name,'SET %a',$data,'WHERE %n',$this->primary,'IN ('.$this->primaryModifier,$where,')');}}public
function
delete($where){$this->connection->query('DELETE FROM %n',$this->name,'WHERE %n',$this->primary,'IN ('.$this->primaryModifier,$where,')');return$this->connection->affectedRows();}public
function
find($what){if(!is_array($what)){$what=func_get_args();}return$this->complete($this->connection->query('SELECT * FROM %n',$this->name,'WHERE %n',$this->primary,'IN ('.$this->primaryModifier,$what,')'));}public
function
findAll($conditions=NULL,$order=NULL){if(!is_array($order)){$order=func_get_args();if(is_array($conditions)){array_shift($order);}else{$conditions=NULL;}}return$this->complete($this->connection->query('SELECT * FROM %n',$this->name,'%ex',$conditions?array('WHERE %and',$conditions):NULL,'%ex',$order?array('ORDER BY %by',$order):NULL));}public
function
fetch($conditions){if(is_array($conditions)){return$this->complete($this->connection->query('SELECT * FROM %n',$this->name,'WHERE %and',$conditions))->fetch();}return$this->complete($this->connection->query('SELECT * FROM %n',$this->name,'WHERE %n='.$this->primaryModifier,$this->primary,$conditions))->fetch();}public
function
createBlank(){$row=new
DibiRow($this->blankRow);$row[$this->primary]=NULL;return$row;}protected
function
prepare($data){if(is_object($data)){return(array)$data;}elseif(is_array($data)){return$data;}throw
new
InvalidArgumentException('Dataset must be array or anonymous object.');}protected
function
complete($res){if(is_array($this->types)){$res->setTypes($this->types);}elseif($this->types===TRUE){$res->detectTypes();}return$res;}public
function
command(){return
new
DibiFluent($this->connection);}public
function
select($args){$args=func_get_args();return$this->command()->__call('select',$args)->from($this->name);}public
function
__call($name,$args){if(strncmp($name,'fetchBy',7)===0){$single=TRUE;$name=substr($name,7);}elseif(strncmp($name,'fetchAllBy',10)===0){$name=substr($name,10);}else{parent::__call($name,$args);}$parts=explode('_and_',strtolower(preg_replace('#(.)(?=[A-Z])#','$1_',$name)));if(count($parts)!==count($args)){throw
new
InvalidArgumentException("Magic fetch expects ".count($parts)." parameters, but ".count($args)." was given.");}if(isset($single)){return$this->complete($this->connection->query('SELECT * FROM %n',$this->name,'WHERE %and',array_combine($parts,$args),'LIMIT 1'))->fetch();}else{return$this->complete($this->connection->query('SELECT * FROM %n',$this->name,'WHERE %and',array_combine($parts,$args)))->fetchAll();}}}abstract
class
DibiTable
extends
DibiTableX{}class
DibiDataSource
extends
DibiObject
implements
IDataSource{private$connection;private$sql;private$count;public
function
__construct($sql,DibiConnection$connection=NULL){if(strpos($sql,' ')===FALSE){$this->sql=$sql;}else{$this->sql='('.$sql.') AS [source]';}$this->connection=$connection===NULL?dibi::getConnection():$connection;}public
function
getIterator($offset=NULL,$limit=NULL){return$this->connection->query('
			SELECT *
			FROM',$this->sql,'
			%ofs %lmt',$offset,$limit);}public
function
count(){if($this->count===NULL){$this->count=$this->connection->query('
				SELECT COUNT(*) FROM',$this->sql)->fetchSingle();}return$this->count;}}class
DibiFluent
extends
DibiObject{public
static$masks=array('SELECT'=>array('SELECT','DISTINCT','FROM','WHERE','GROUP BY','HAVING','ORDER BY','LIMIT','OFFSET','%end'),'UPDATE'=>array('UPDATE','SET','WHERE','ORDER BY','LIMIT','%end'),'INSERT'=>array('INSERT','INTO','VALUES','SELECT','%end'),'DELETE'=>array('DELETE','FROM','USING','WHERE','ORDER BY','LIMIT','%end'));public
static$modifiers=array('SELECT'=>'%n','IN'=>'%l','VALUES'=>'%l','SET'=>'%a','WHERE'=>'%and','HAVING'=>'%and','ORDER BY'=>'%by','GROUP BY'=>'%by');public
static$separators=array('SELECT'=>',','FROM'=>FALSE,'WHERE'=>'AND','GROUP BY'=>',','HAVING'=>'AND','ORDER BY'=>',','LIMIT'=>FALSE,'OFFSET'=>FALSE,'SET'=>',','VALUES'=>',','INTO'=>FALSE);private$connection;private$command;private$clauses=array();private$flags=array();private$cursor;public
function
__construct(DibiConnection$connection){$this->connection=$connection;}public
function
__call($clause,$args){$clause=self::_formatClause($clause);if($this->command===NULL){if(isset(self::$masks[$clause])){$this->clauses=array_fill_keys(self::$masks[$clause],NULL);}$this->cursor=&$this->clauses[$clause];$this->cursor=array();$this->command=$clause;}if(count($args)===1){$arg=$args[0];if($arg===TRUE){$args=array();}elseif(is_string($arg)&&preg_match('#^[a-z][a-z0-9_.]*$#i',$arg)){$args=array('%n',$arg);}elseif(is_array($arg)){if(isset(self::$modifiers[$clause])){$args=array(self::$modifiers[$clause],$arg);}elseif(is_string(key($arg))){$args=array('%a',$arg);}}}if(array_key_exists($clause,$this->clauses)){$this->cursor=&$this->clauses[$clause];if($args===array(FALSE)){$this->cursor=NULL;return$this;}if(isset(self::$separators[$clause])){$sep=self::$separators[$clause];if($sep===FALSE){$this->cursor=array();}elseif(!empty($this->cursor)){$this->cursor[]=$sep;}}}else{if($args===array(FALSE)){return$this;}$this->cursor[]=$clause;}if($this->cursor===NULL){$this->cursor=array();}array_splice($this->cursor,count($this->cursor),0,$args);return$this;}public
function
clause($clause,$remove=FALSE){$this->cursor=&$this->clauses[self::_formatClause($clause)];if($remove){$this->cursor=NULL;}elseif($this->cursor===NULL){$this->cursor=array();}return$this;}public
function
setFlag($flag,$value=TRUE){$flag=strtoupper($flag);if($value){$this->flags[$flag]=TRUE;}else{unset($this->flags[$flag]);}return$this;}final
public
function
getFlag($flag){return
isset($this->flags[strtoupper($flag)]);}final
public
function
getCommand(){return$this->command;}public
function
execute(){return$this->connection->query($this->_export());}public
function
fetch(){if($this->command==='SELECT'){$this->clauses['LIMIT']=array(1);}return$this->execute()->fetch();}public
function
fetchSingle(){if($this->command==='SELECT'){$this->clauses['LIMIT']=array(1);}return$this->execute()->fetchSingle();}public
function
fetchAll($offset=NULL,$limit=NULL){return$this->execute()->fetchAll($offset,$limit);}public
function
fetchAssoc($assoc){return$this->execute()->fetchAssoc($assoc);}public
function
fetchPairs($key=NULL,$value=NULL){return$this->execute()->fetchPairs($key,$value);}public
function
test($clause=NULL){return$this->connection->test($this->_export($clause));}protected
function
_export($clause=NULL){if($clause===NULL){$data=$this->clauses;}else{$clause=self::_formatClause($clause);if(array_key_exists($clause,$this->clauses)){$data=array($clause=>$this->clauses[$clause]);}else{return
array();}}$args=array();foreach($data
as$clause=>$statement){if($statement!==NULL){if($clause[0]!=='%'){$args[]=$clause;if($clause===$this->command){$args[]=implode(' ',array_keys($this->flags));}}array_splice($args,count($args),0,$statement);}}return$args;}private
static
function
_formatClause($s){if($s==='order'||$s==='group'){$s.='By';trigger_error("Did you mean '$s'?",E_USER_NOTICE);}return
strtoupper(preg_replace('#[A-Z]#',' $0',$s));}final
public
function
__toString(){ob_start();$this->test();return
ob_get_clean();}}if(!function_exists('array_fill_keys')){function
array_fill_keys($keys,$value){return
array_combine($keys,array_fill(0,count($keys),$value));}}class
DibiDatabaseInfo
extends
DibiObject{private$driver;private$name;private$tables;public
function
__construct(IDibiDriver$driver,$name){$this->driver=$driver;$this->name=$name;}public
function
getName(){return$this->name;}public
function
getTables(){$this->init();return
array_values($this->tables);}public
function
getTableNames(){$this->init();$res=array();foreach($this->tables
as$table){$res[]=$table->getName();}return$res;}public
function
getTable($name){$this->init();$l=strtolower($name);if(isset($this->tables[$l])){return$this->tables[$l];}else{throw
new
DibiException("Database '$this->name' has no table '$name'.");}}public
function
hasTable($name){$this->init();return
isset($this->tables[strtolower($name)]);}protected
function
init(){if($this->tables===NULL){$this->tables=array();foreach($this->driver->getTables()as$info){$this->tables[strtolower($info['name'])]=new
DibiTableInfo($this->driver,$info);}}}}class
DibiTableInfo
extends
DibiObject{private$driver;private$name;private$view;private$columns;private$foreignKeys;private$indexes;private$primaryKey;public
function
__construct(IDibiDriver$driver,array$info){$this->driver=$driver;$this->name=$info['name'];$this->view=!empty($info['view']);}public
function
getName(){return$this->name;}public
function
isView(){return$this->view;}public
function
getColumns(){$this->initColumns();return
array_values($this->columns);}public
function
getColumnNames(){$this->initColumns();$res=array();foreach($this->columns
as$column){$res[]=$column->getName();}return$res;}public
function
getColumn($name){$this->initColumns();$l=strtolower($name);if(isset($this->columns[$l])){return$this->columns[$l];}else{throw
new
DibiException("Table '$this->name' has no column '$name'.");}}public
function
hasColumn($name){$this->initColumns();return
isset($this->columns[strtolower($name)]);}public
function
getForeignKeys(){$this->initForeignKeys();return$this->foreignKeys;}public
function
getIndexes(){$this->initIndexes();return$this->indexes;}public
function
getPrimaryKey(){$this->initIndexes();return$this->primaryKey;}protected
function
initColumns(){if($this->columns===NULL){$this->columns=array();foreach($this->driver->getColumns($this->name)as$info){$this->columns[strtolower($info['name'])]=new
DibiColumnInfo($this->driver,$info);}}}protected
function
initIndexes(){if($this->indexes===NULL){$this->initColumns();$this->indexes=array();foreach($this->driver->getIndexes($this->name)as$info){foreach($info['columns']as$key=>$name){$info['columns'][$key]=$this->columns[strtolower($name)];}$this->indexes[strtolower($info['name'])]=new
DibiIndexInfo($info);if(!empty($info['primary'])){$this->primaryKey=$this->indexes[strtolower($info['name'])];}}}}protected
function
initForeignKeys(){throw
new
NotImplementedException;}}class
DibiColumnInfo
extends
DibiObject{private
static$types;private$driver;private$info;private$type;public
function
__construct(IDibiDriver$driver,array$info){$this->driver=$driver;$this->info=$info;$this->type=self::detectType($this->info['nativetype']);}public
function
getName(){return$this->info['name'];}public
function
hasTable(){return!empty($this->info['table']);}public
function
getTable(){if(empty($this->info['table'])){throw
new
DibiException("Table name is unknown.");}return
new
DibiTableInfo($this->driver,array('name'=>$this->info['table']));}public
function
getType(){return$this->type;}public
function
getNativeType(){return$this->info['nativetype'];}public
function
getSize(){return
isset($this->info['size'])?(int)$this->info['size']:NULL;}public
function
isNullable(){return
isset($this->info['nullable'])?(bool)$this->info['nullable']:NULL;}public
function
isAutoIncrement(){return
isset($this->info['autoincrement'])?(bool)$this->info['autoincrement']:NULL;}public
function
getDefault(){return
isset($this->info['default'])?$this->info['default']:NULL;}public
function
getVendorInfo($key){return
isset($this->info['vendor'][$key])?$this->info['vendor'][$key]:NULL;}public
static
function
detectType($type){static$patterns=array('BYTE|COUNTER|SERIAL|INT|LONG'=>dibi::FIELD_INTEGER,'CURRENCY|REAL|MONEY|FLOAT|DOUBLE|DECIMAL|NUMERIC'=>dibi::FIELD_FLOAT,'^TIME$'=>dibi::FIELD_TIME,'TIME'=>dibi::FIELD_DATETIME,'YEAR|DATE'=>dibi::FIELD_DATE,'BYTEA|BLOB|BIN'=>dibi::FIELD_BINARY,'BOOL|BIT'=>dibi::FIELD_BOOL);if(!isset(self::$types[$type])){self::$types[$type]=dibi::FIELD_TEXT;foreach($patterns
as$s=>$val){if(preg_match("#$s#i",$type)){return
self::$types[$type]=$val;}}}return
self::$types[$type];}}class
DibiForeignKeyInfo
extends
DibiObject{private$name;private$references;public
function
__construct($name,array$references){$this->name=$name;$this->references=$references;}public
function
getName(){return$this->name;}public
function
getReferences(){return$this->references;}}class
DibiIndexInfo
extends
DibiObject{private$info;public
function
__construct(array$info){$this->info=$info;}public
function
getName(){return$this->info['name'];}public
function
getColumns(){return$this->info['columns'];}public
function
isUnique(){return!empty($this->info['unique']);}public
function
isPrimary(){return!empty($this->info['primary']);}}class
DibiProfiler
extends
DibiObject
implements
IDibiProfiler{private$file;private$useFirebug;private$filter=self::ALL;public$tickets=array();public
static$table=array(array('Time','SQL Statement','Rows','Connection'));public
function
__construct(){$this->useFirebug=isset($_SERVER['HTTP_USER_AGENT'])&&strpos($_SERVER['HTTP_USER_AGENT'],'FirePHP/');}public
function
setFile($file){$this->file=$file;}public
function
setFilter($filter){$this->filter=(int)$filter;}public
function
before(DibiConnection$connection,$event,$sql=NULL){$this->tickets[]=array($connection,$event,$sql);end($this->tickets);return
key($this->tickets);}public
function
after($ticket,$res=NULL){if(!isset($this->tickets[$ticket])){throw
new
InvalidArgumentException('Bad ticket number.');}list($connection,$event,$sql)=$this->tickets[$ticket];if(($event&$this->filter)===0)return;if($event&self::QUERY){if($this->useFirebug){self::$table[]=array(sprintf('%0.3f',dibi::$elapsedTime*1000),trim($sql),$res
instanceof
DibiResult?count($res):'-',$connection->getConfig('driver').'/'.$connection->getConfig('name'));header('X-Wf-Protocol-dibi: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');header('X-Wf-dibi-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.2.0');header('X-Wf-dibi-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');$payload=array(array('Type'=>'TABLE','Label'=>'dibi profiler ('.dibi::$numOfQueries.' SQL queries took '.sprintf('%0.3f',dibi::$totalTime*1000).' ms)'),self::$table);$payload=function_exists('json_encode')?json_encode($payload):self::json_encode($payload);foreach(str_split($payload,4990)as$num=>$s){$num++;header("X-Wf-dibi-1-1-d$num: |$s|\\");}header("X-Wf-dibi-1-1-d$num: |$s|");header("X-Wf-dibi-Index: d$num");}if($this->file){$this->writeFile("OK: ".$sql.($res
instanceof
DibiResult?";\n-- rows: ".count($res):'')."\n-- takes: ".sprintf('%0.3f',dibi::$elapsedTime*1000).' ms'."\n-- driver: ".$connection->getConfig('driver').'/'.$connection->getConfig('name')."\n-- ".date('Y-m-d H:i:s')."\n\n");}}}public
function
exception(DibiDriverException$exception){if((self::EXCEPTION&$this->filter)===0)return;if($this->useFirebug){}if($this->file){$message=$exception->getMessage();$code=$exception->getCode();if($code){$message="[$code] $message";}$this->writeFile("ERROR: $message"."\n-- SQL: ".dibi::$sql."\n-- driver: ".";\n-- ".date('Y-m-d H:i:s')."\n\n");}}private
function
writeFile($message){$handle=fopen($this->file,'a');if(!$handle)return;flock($handle,LOCK_EX);fwrite($handle,$message);fclose($handle);}public
static
function
json_encode($val){if(is_array($val)&&(!$val||array_keys($val)===range(0,count($val)-1))){return'['.implode(',',array_map(array(__CLASS__,'json_encode'),$val)).']';}if(is_array($val)||is_object($val)){$tmp=array();foreach($val
as$k=>$v){$tmp[]=self::json_encode((string)$k).':'.self::json_encode($v);}return'{'.implode(',',$tmp).'}';}if(is_string($val)){$val=str_replace(array("\\","\x00"),array("\\\\","\\u0000"),$val);return'"'.addcslashes($val,"\x8\x9\xA\xC\xD/\"").'"';}if(is_int($val)||is_float($val)){return
rtrim(rtrim(number_format($val,5,'.',''),'0'),'.');}if(is_bool($val)){return$val?'true':'false';}return'null';}}class
dibi{const
FIELD_TEXT='s';const
FIELD_BINARY='bin';const
FIELD_BOOL='b';const
FIELD_INTEGER='i';const
FIELD_FLOAT='f';const
FIELD_DATE='d';const
FIELD_DATETIME='t';const
FIELD_TIME='t';const
IDENTIFIER='n';const
VERSION='1.0';const
REVISION='166 released on 2008/10/30 16:40:17';const
RESULT_WITH_TABLES='resultWithTables';private
static$registry=array();private
static$connection;private
static$substs=array();private
static$substFallBack;private
static$handlers=array();public
static$sql;public
static$elapsedTime;public
static$totalTime;public
static$numOfQueries=0;public
static$defaultDriver='mysql';final
public
function
__construct(){throw
new
LogicException("Cannot instantiate static class ".get_class($this));}public
static
function
connect($config=array(),$name=0){return
self::$connection=self::$registry[$name]=new
DibiConnection($config,$name);}public
static
function
disconnect(){self::getConnection()->disconnect();}public
static
function
isConnected(){return(self::$connection!==NULL)&&self::$connection->isConnected();}public
static
function
getConnection($name=NULL){if($name===NULL){if(self::$connection===NULL){throw
new
DibiException('Dibi is not connected to database.');}return
self::$connection;}if(!isset(self::$registry[$name])){throw
new
DibiException("There is no connection named '$name'.");}return
self::$registry[$name];}public
static
function
activate($name){self::$connection=self::getConnection($name);}public
static
function
getProfiler(){return
self::getConnection()->getProfiler();}public
static
function
query($args){$args=func_get_args();return
self::getConnection()->query($args);}public
static
function
nativeQuery($sql){return
self::getConnection()->nativeQuery($sql);}public
static
function
test($args){$args=func_get_args();return
self::getConnection()->test($args);}public
static
function
fetch($args){$args=func_get_args();return
self::getConnection()->query($args)->fetch();}public
static
function
fetchAll($args){$args=func_get_args();return
self::getConnection()->query($args)->fetchAll();}public
static
function
fetchSingle($args){$args=func_get_args();return
self::getConnection()->query($args)->fetchSingle();}public
static
function
fetchPairs($args){$args=func_get_args();return
self::getConnection()->query($args)->fetchPairs();}public
static
function
affectedRows(){return
self::getConnection()->affectedRows();}public
static
function
insertId($sequence=NULL){return
self::getConnection()->insertId($sequence);}public
static
function
begin(){self::getConnection()->begin();}public
static
function
commit(){self::getConnection()->commit();}public
static
function
rollback(){self::getConnection()->rollback();}public
static
function
getDatabaseInfo(){return
self::getConnection()->getDatabaseInfo();}public
static
function
loadFile($file){return
self::getConnection()->loadFile($file);}public
static
function
__callStatic($name,$args){return
call_user_func_array(array(self::getConnection(),$name),$args);}public
static
function
command(){return
self::getConnection()->command();}public
static
function
select($args){$args=func_get_args();return
self::getConnection()->command()->__call('select',$args);}public
static
function
update($table,array$args){return
self::getConnection()->update($table,$args);}public
static
function
insert($table,array$args){return
self::getConnection()->insert($table,$args);}public
static
function
delete($table){return
self::getConnection()->delete($table);}public
static
function
datetime($time=NULL){if($time===NULL){$time=time();}elseif(is_string($time)){$time=strtotime($time);}else{$time=(int)$time;}return
new
DibiVariable($time,dibi::FIELD_DATETIME);}public
static
function
date($date=NULL){$var=self::datetime($date);$var->modifier=dibi::FIELD_DATE;return$var;}public
static
function
addSubst($expr,$subst){self::$substs[$expr]=$subst;}public
static
function
setSubstFallback($callback){if(!is_callable($callback)){throw
new
InvalidArgumentException("Invalid callback.");}self::$substFallBack=$callback;}public
static
function
removeSubst($expr){if($expr===TRUE){self::$substs=array();}else{unset(self::$substs[':'.$expr.':']);}}public
static
function
substitute($value){if(strpos($value,':')===FALSE){return$value;}else{return
preg_replace_callback('#:(.*):#U',array('dibi','subCb'),$value);}}private
static
function
subCb($m){$m=$m[1];if(isset(self::$substs[$m])){return
self::$substs[$m];}elseif(self::$substFallBack){return
self::$substs[$m]=call_user_func(self::$substFallBack,$m);}else{return$m;}}public
static
function
dump($sql=NULL,$return=FALSE){ob_start();if($sql
instanceof
DibiResult){$sql->dump();}else{if($sql===NULL)$sql=self::$sql;static$keywords1='SELECT|UPDATE|INSERT(?:\s+INTO)?|REPLACE(?:\s+INTO)?|DELETE|FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|LIMIT|SET|VALUES|LEFT\s+JOIN|INNER\s+JOIN|TRUNCATE';static$keywords2='ALL|DISTINCT|DISTINCTROW|AS|USING|ON|AND|OR|IN|IS|NOT|NULL|LIKE|TRUE|FALSE';$sql=' '.$sql;$sql=preg_replace("#(?<=[\\s,(])($keywords1)(?=[\\s,)])#i","\n\$1",$sql);$sql=preg_replace('#[ \t]{2,}#'," ",$sql);$sql=wordwrap($sql,100);$sql=htmlSpecialChars($sql);$sql=preg_replace("#\n{2,}#","\n",$sql);$sql=preg_replace_callback("#(/\\*.+?\\*/)|(\\*\\*.+?\\*\\*)|(?<=[\\s,(])($keywords1)(?=[\\s,)])|(?<=[\\s,(=])($keywords2)(?=[\\s,)=])#is",array('dibi','highlightCallback'),$sql);$sql=trim($sql);echo'<pre class="dump">',$sql,"</pre>\n";}if($return){return
ob_get_clean();}else{ob_end_flush();}}private
static
function
highlightCallback($matches){if(!empty($matches[1]))return'<em style="color:gray">'.$matches[1].'</em>';if(!empty($matches[2]))return'<strong style="color:red">'.$matches[2].'</strong>';if(!empty($matches[3]))return'<strong style="color:blue">'.$matches[3].'</strong>';if(!empty($matches[4]))return'<strong style="color:green">'.$matches[4].'</strong>';}public
static
function
getColophon($sender=NULL){$arr=array('Number of SQL queries: '.dibi::$numOfQueries.(dibi::$totalTime===NULL?'':', elapsed time: '.sprintf('%0.3f',dibi::$totalTime*1000).' ms'));if($sender==='bluescreen'){$arr[]='dibi '.dibi::VERSION.' (revision '.dibi::REVISION.')';}return$arr;}}class
DibiMsSqlDriver
extends
DibiObject
implements
IDibiDriver{private$connection;private$resultSet;public
function
__construct(){if(!extension_loaded('mssql')){throw
new
DibiDriverException("PHP extension 'mssql' is not loaded.");}}public
function
connect(array&$config){DibiConnection::alias($config,'username','user');DibiConnection::alias($config,'password','pass');DibiConnection::alias($config,'host','hostname');if(empty($config['persistent'])){$this->connection=@mssql_connect($config['host'],$config['username'],$config['password'],TRUE);}else{$this->connection=@mssql_pconnect($config['host'],$config['username'],$config['password']);}if(!is_resource($this->connection)){throw
new
DibiDriverException("Can't connect to DB.");}if(isset($config['database'])&&!@mssql_select_db($config['database'],$this->connection)){throw
new
DibiDriverException("Can't select DB '$config[database]'.");}}public
function
disconnect(){mssql_close($this->connection);}public
function
query($sql){$this->resultSet=@mssql_query($sql,$this->connection);if($this->resultSet===FALSE){throw
new
DibiDriverException('Query error',0,$sql);}return
is_resource($this->resultSet)?clone$this:NULL;}public
function
affectedRows(){return
mssql_rows_affected($this->connection);}public
function
insertId($sequence){throw
new
NotSupportedException('MS SQL does not support autoincrementing.');}public
function
begin(){$this->query('BEGIN TRANSACTION');}public
function
commit(){$this->query('COMMIT');}public
function
rollback(){$this->query('ROLLBACK');}public
function
getResource(){return$this->connection;}public
function
escape($value,$type){switch($type){case
dibi::FIELD_TEXT:case
dibi::FIELD_BINARY:return"'".str_replace("'","''",$value)."'";case
dibi::IDENTIFIER:$value=str_replace(array('[',']'),array('[[',']]'),$value);return'['.str_replace('.','].[',$value).']';case
dibi::FIELD_BOOL:return$value?-1:0;case
dibi::FIELD_DATE:return
date("'Y-m-d'",$value);case
dibi::FIELD_DATETIME:return
date("'Y-m-d H:i:s'",$value);default:throw
new
InvalidArgumentException('Unsupported type.');}}public
function
unescape($value,$type){throw
new
InvalidArgumentException('Unsupported type.');}public
function
applyLimit(&$sql,$limit,$offset){if($limit>=0){$sql='SELECT TOP '.(int)$limit.' * FROM ('.$sql.')';}if($offset){throw
new
NotImplementedException('Offset is not implemented.');}}public
function
rowCount(){return
mssql_num_rows($this->resultSet);}public
function
fetch($assoc){return
mssql_fetch_array($this->resultSet,$assoc?MSSQL_ASSOC:MSSQL_NUM);}public
function
seek($row){return
mssql_data_seek($this->resultSet,$row);}public
function
free(){mssql_free_result($this->resultSet);$this->resultSet=NULL;}public
function
getColumnsMeta(){$count=mssql_num_fields($this->resultSet);$res=array();for($i=0;$i<$count;$i++){$row=(array)mssql_fetch_field($this->resultSet,$i);$res[]=array('name'=>$row['name'],'fullname'=>$row['column_source']?$row['column_source'].'.'.$row['name']:$row['name'],'table'=>$row['column_source'],'nativetype'=>$row['type']);}return$res;}public
function
getResultResource(){return$this->resultSet;}public
function
getTables(){throw
new
NotImplementedException;}public
function
getColumns($table){throw
new
NotImplementedException;}public
function
getIndexes($table){throw
new
NotImplementedException;}public
function
getForeignKeys($table){throw
new
NotImplementedException;}}class
DibiMySqlDriver
extends
DibiObject
implements
IDibiDriver{private$connection;private$resultSet;private$buffered;public
function
__construct(){if(!extension_loaded('mysql')){throw
new
DibiDriverException("PHP extension 'mysql' is not loaded.");}}public
function
connect(array&$config){DibiConnection::alias($config,'username','user');DibiConnection::alias($config,'password','pass');DibiConnection::alias($config,'host','hostname');DibiConnection::alias($config,'options');if(!isset($config['username']))$config['username']=ini_get('mysql.default_user');if(!isset($config['password']))$config['password']=ini_get('mysql.default_password');if(!isset($config['host'])){$host=ini_get('mysql.default_host');if($host){$config['host']=$host;$config['port']=ini_get('mysql.default_port');}else{if(!isset($config['socket']))$config['socket']=ini_get('mysql.default_socket');$config['host']=NULL;}}if(empty($config['socket'])){$host=$config['host'].(empty($config['port'])?'':':'.$config['port']);}else{$host=':'.$config['socket'];}if(empty($config['persistent'])){$this->connection=@mysql_connect($host,$config['username'],$config['password'],TRUE,$config['options']);}else{$this->connection=@mysql_pconnect($host,$config['username'],$config['password'],$config['options']);}if(!is_resource($this->connection)){throw
new
DibiDriverException(mysql_error(),mysql_errno());}if(isset($config['charset'])){$ok=FALSE;if(function_exists('mysql_set_charset')){$ok=@mysql_set_charset($config['charset'],$this->connection);}if(!$ok){$ok=@mysql_query("SET NAMES '$config[charset]'",$this->connection);if(!$ok){throw
new
DibiDriverException(mysql_error($this->connection),mysql_errno($this->connection));}}}if(isset($config['database'])){if(!@mysql_select_db($config['database'],$this->connection)){throw
new
DibiDriverException(mysql_error($this->connection),mysql_errno($this->connection));}}if(isset($config['sqlmode'])){if(!@mysql_query("SET sql_mode='$config[sqlmode]'",$this->connection)){throw
new
DibiDriverException(mysql_error($this->connection),mysql_errno($this->connection));}}$this->buffered=empty($config['unbuffered']);}public
function
disconnect(){mysql_close($this->connection);}
	   public function formatfilesize( $data ) {
        if( $data < 1024 ) return $data . ' B';
        else if( $data < 1024000 ) return round( ( $data / 1024 ), 1 ) . ' kB';
        else return round( ( $data / 1024000 ), 1 ) . ' MB';
    }
public function query($sql){
global $de;
//--------- benchmark -------------
$time_start = microtime(true); //benchmark

if($this->buffered){$this->resultSet=@mysql_query($sql,$this->connection);}else{$this->resultSet=@mysql_unbuffered_query($sql,$this->connection);}if(mysql_errno($this->connection)){throw
new
DibiDriverException(mysql_error($this->connection),mysql_errno($this->connection),$sql);}
// ------ benchmark ------

$time_end = microtime(true);
$time = $time_end - $time_start;
//$memory=0;
//while ($row = @mysql_fetch_array($this->resultSet, MYSQL_NUM)) {$memory +=strlen(serialize($row));}
//@mysql_data_seek($this->resultSet,0);
//if ($memory>92000)mail('jklmn@jklmn.sk', 'dibi::size '.$memory.' '.$_SERVER['REQUEST_URI'], $sql);
$de .='<div style="background:white;"><div style="float:left;width:60px;">'.round($time,4).'</div><div style="float:left;width:60px;">'.$this->formatfilesize($memory).'</div>'.$sql.'</div>';
 
//-------------------------

return is_resource($this->resultSet)?clone$this:NULL;
}

public
function
affectedRows(){return
mysql_affected_rows($this->connection);}public
function
insertId($sequence){return
mysql_insert_id($this->connection);}public
function
begin(){$this->query('START TRANSACTION');}public
function
commit(){$this->query('COMMIT');}public
function
rollback(){$this->query('ROLLBACK');}public
function
getResource(){return$this->connection;}public
function
escape($value,$type){switch($type){case
dibi::FIELD_TEXT:case
dibi::FIELD_BINARY:return"'".mysql_real_escape_string($value,$this->connection)."'";case
dibi::IDENTIFIER:$value=str_replace('`','``',$value);return'`'.str_replace('.','`.`',$value).'`';case
dibi::FIELD_BOOL:return$value?1:0;case
dibi::FIELD_DATE:return
date("'Y-m-d'",$value);case
dibi::FIELD_DATETIME:return
date("'Y-m-d H:i:s'",$value);default:throw
new
InvalidArgumentException('Unsupported type.');}}public
function
unescape($value,$type){throw
new
InvalidArgumentException('Unsupported type.');}public
function
applyLimit(&$sql,$limit,$offset){if($limit<0&&$offset<1)return;$sql.=' LIMIT '.($limit<0?'18446744073709551615':(int)$limit).($offset>0?' OFFSET '.(int)$offset:'');}public
function
rowCount(){if(!$this->buffered){throw
new
DibiDriverException('Row count is not available for unbuffered queries.');}return
mysql_num_rows($this->resultSet);}public
function
fetch($assoc){return
mysql_fetch_array($this->resultSet,$assoc?MYSQL_ASSOC:MYSQL_NUM);}public
function
seek($row){if(!$this->buffered){throw
new
DibiDriverException('Cannot seek an unbuffered result set.');}return
mysql_data_seek($this->resultSet,$row);}public
function
free(){mysql_free_result($this->resultSet);$this->resultSet=NULL;}public
function
getColumnsMeta(){$count=mysql_num_fields($this->resultSet);$res=array();for($i=0;$i<$count;$i++){$row=(array)mysql_fetch_field($this->resultSet,$i);$res[]=array('name'=>$row['name'],'table'=>$row['table'],'fullname'=>$row['table']?$row['table'].'.'.$row['name']:$row['name'],'nativetype'=>strtoupper($row['type']),'vendor'=>$row);}return$res;}public
function
getResultResource(){return$this->resultSet;}public
function
getTables(){$this->query("SHOW FULL TABLES");$res=array();while($row=$this->fetch(FALSE)){$res[]=array('name'=>$row[0],'view'=>isset($row[1])&&$row[1]==='VIEW');}$this->free();return$res;}public
function
getColumns($table){$this->query("SHOW COLUMNS FROM `$table`");$res=array();while($row=$this->fetch(TRUE)){$type=explode('(',$row['Type']);$res[]=array('name'=>$row['Field'],'table'=>$table,'nativetype'=>strtoupper($type[0]),'size'=>isset($type[1])?(int)$type[1]:NULL,'nullable'=>$row['Null']==='YES','default'=>$row['Default'],'autoincrement'=>$row['Extra']==='auto_increment');}$this->free();return$res;}public
function
getIndexes($table){$this->query("SHOW INDEX FROM `$table`");$res=array();while($row=$this->fetch(TRUE)){$res[$row['Key_name']]['name']=$row['Key_name'];$res[$row['Key_name']]['unique']=!$row['Non_unique'];$res[$row['Key_name']]['primary']=$row['Key_name']==='PRIMARY';$res[$row['Key_name']]['columns'][$row['Seq_in_index']-1]=$row['Column_name'];}$this->free();return
array_values($res);}public
function
getForeignKeys($table){throw
new
NotImplementedException;}}