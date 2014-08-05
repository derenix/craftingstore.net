<?php /* https://code.google.com/p/isemail/downloads/list */ if(!defined('ISEMAIL_VALID')){define('ISEMAIL_VALID_CATEGORY',1);define('ISEMAIL_DNSWARN',7);define('ISEMAIL_RFC5321',15);define('ISEMAIL_CFWS',31);define('ISEMAIL_DEPREC',63);define('ISEMAIL_RFC5322',127);define('ISEMAIL_ERR',255);define('ISEMAIL_VALID',0);define('ISEMAIL_DNSWARN_NO_MX_RECORD',5);define('ISEMAIL_DNSWARN_NO_RECORD',6);define('ISEMAIL_RFC5321_TLD',9);define('ISEMAIL_RFC5321_TLDNUMERIC',10);define('ISEMAIL_RFC5321_QUOTEDSTRING',11);define('ISEMAIL_RFC5321_ADDRESSLITERAL',12);define('ISEMAIL_RFC5321_IPV6DEPRECATED',13);define('ISEMAIL_CFWS_COMMENT',17);define('ISEMAIL_CFWS_FWS',18);define('ISEMAIL_DEPREC_LOCALPART',33);define('ISEMAIL_DEPREC_FWS',34);define('ISEMAIL_DEPREC_QTEXT',35);define('ISEMAIL_DEPREC_QP',36);define('ISEMAIL_DEPREC_COMMENT',37);define('ISEMAIL_DEPREC_CTEXT',38);define('ISEMAIL_DEPREC_CFWS_NEAR_AT',49);define('ISEMAIL_RFC5322_DOMAIN',65);define('ISEMAIL_RFC5322_TOOLONG',66);define('ISEMAIL_RFC5322_LOCAL_TOOLONG',67);define('ISEMAIL_RFC5322_DOMAIN_TOOLONG',68);define('ISEMAIL_RFC5322_LABEL_TOOLONG',69);define('ISEMAIL_RFC5322_DOMAINLITERAL',70);define('ISEMAIL_RFC5322_DOMLIT_OBSDTEXT',71);define('ISEMAIL_RFC5322_IPV6_GRPCOUNT',72);define('ISEMAIL_RFC5322_IPV6_2X2XCOLON',73);define('ISEMAIL_RFC5322_IPV6_BADCHAR',74);define('ISEMAIL_RFC5322_IPV6_MAXGRPS',75);define('ISEMAIL_RFC5322_IPV6_COLONSTRT',76);define('ISEMAIL_RFC5322_IPV6_COLONEND',77);define('ISEMAIL_ERR_EXPECTING_DTEXT',129);define('ISEMAIL_ERR_NOLOCALPART',130);define('ISEMAIL_ERR_NODOMAIN',131);define('ISEMAIL_ERR_CONSECUTIVEDOTS',132);define('ISEMAIL_ERR_ATEXT_AFTER_CFWS',133);define('ISEMAIL_ERR_ATEXT_AFTER_QS',134);define('ISEMAIL_ERR_ATEXT_AFTER_DOMLIT',135);define('ISEMAIL_ERR_EXPECTING_QPAIR',136);define('ISEMAIL_ERR_EXPECTING_ATEXT',137);define('ISEMAIL_ERR_EXPECTING_QTEXT',138);define('ISEMAIL_ERR_EXPECTING_CTEXT',139);define('ISEMAIL_ERR_BACKSLASHEND',140);define('ISEMAIL_ERR_DOT_START',141);define('ISEMAIL_ERR_DOT_END',142);define('ISEMAIL_ERR_DOMAINHYPHENSTART',143);define('ISEMAIL_ERR_DOMAINHYPHENEND',144);define('ISEMAIL_ERR_UNCLOSEDQUOTEDSTR',145);define('ISEMAIL_ERR_UNCLOSEDCOMMENT',146);define('ISEMAIL_ERR_UNCLOSEDDOMLIT',147);define('ISEMAIL_ERR_FWS_CRLF_X2',148);define('ISEMAIL_ERR_FWS_CRLF_END',149);define('ISEMAIL_ERR_CR_NO_LF',150);define('ISEMAIL_THRESHOLD',16);define('ISEMAIL_COMPONENT_LOCALPART',0);define('ISEMAIL_COMPONENT_DOMAIN',1);define('ISEMAIL_COMPONENT_LITERAL',2);define('ISEMAIL_CONTEXT_COMMENT',3);define('ISEMAIL_CONTEXT_FWS',4);define('ISEMAIL_CONTEXT_QUOTEDSTRING',5);define('ISEMAIL_CONTEXT_QUOTEDPAIR',6);define('ISEMAIL_STRING_AT','@');define('ISEMAIL_STRING_BACKSLASH','\\');define('ISEMAIL_STRING_DOT','.');define('ISEMAIL_STRING_DQUOTE','"');define('ISEMAIL_STRING_OPENPARENTHESIS','(');define('ISEMAIL_STRING_CLOSEPARENTHESIS',')');define('ISEMAIL_STRING_OPENSQBRACKET','[');define('ISEMAIL_STRING_CLOSESQBRACKET',']');define('ISEMAIL_STRING_HYPHEN','-');define('ISEMAIL_STRING_COLON',':');define('ISEMAIL_STRING_DOUBLECOLON','::');define('ISEMAIL_STRING_SP',' ');define('ISEMAIL_STRING_HTAB',"\t");define('ISEMAIL_STRING_CR',"\r");define('ISEMAIL_STRING_LF',"\n");define('ISEMAIL_STRING_IPV6TAG','IPv6:');define('ISEMAIL_STRING_SPECIALS','()<>[]:;@\\,."');}function is_email(&$email,$checkDNS=false,$errorlevel=false,&$parsedata=array()){

$email=str_replace(array("\r","\n"),array('',''),$email);
$email=preg_replace("/[^a-zäöüA-ZÄÖÜ0-9@!#$%&'*?^_`.{|}~+-]/","",$email);

if(is_bool($errorlevel)){$threshold=ISEMAIL_VALID;$diagnose=(bool)$errorlevel;}else{$diagnose=true;switch((int)$errorlevel){case E_WARNING:$threshold=ISEMAIL_THRESHOLD;break;case E_ERROR:$threshold=ISEMAIL_VALID;break;default:$threshold=(int)$errorlevel;}}$return_status=array(ISEMAIL_VALID);$raw_length=strlen($email);$context=ISEMAIL_COMPONENT_LOCALPART;$context_stack=array($context);$context_prior=ISEMAIL_COMPONENT_LOCALPART;$token='';$token_prior='';$parsedata=array(ISEMAIL_COMPONENT_LOCALPART=>'',ISEMAIL_COMPONENT_DOMAIN=>'');$atomlist=array(ISEMAIL_COMPONENT_LOCALPART=>array(''),ISEMAIL_COMPONENT_DOMAIN=>array(''));$element_count=0;$element_len=0;$hyphen_flag=false;$end_or_die=false;for($i=0;$i<$raw_length;$i++){$token=$email[$i];switch($context){case ISEMAIL_COMPONENT_LOCALPART:switch($token){case ISEMAIL_STRING_OPENPARENTHESIS:if($element_len===0)$return_status[]=($element_count===0)?ISEMAIL_CFWS_COMMENT:ISEMAIL_DEPREC_COMMENT;else{$return_status[]=ISEMAIL_CFWS_COMMENT;$end_or_die=true;}$context_stack[]=$context;$context=ISEMAIL_CONTEXT_COMMENT;break;case ISEMAIL_STRING_DOT:if($element_len===0)$return_status[]=($element_count===0)?ISEMAIL_ERR_DOT_START:ISEMAIL_ERR_CONSECUTIVEDOTS;else if($end_or_die)$return_status[]=ISEMAIL_DEPREC_LOCALPART;$end_or_die=false;$element_len=0;$element_count++;$parsedata[ISEMAIL_COMPONENT_LOCALPART].=$token;$atomlist[ISEMAIL_COMPONENT_LOCALPART][$element_count]='';break;case ISEMAIL_STRING_DQUOTE:if($element_len===0){$return_status[]=($element_count===0)?ISEMAIL_RFC5321_QUOTEDSTRING:ISEMAIL_DEPREC_LOCALPART;$parsedata[ISEMAIL_COMPONENT_LOCALPART].=$token;$atomlist[ISEMAIL_COMPONENT_LOCALPART][$element_count].=$token;$element_len++;$end_or_die=true;$context_stack[]=$context;$context=ISEMAIL_CONTEXT_QUOTEDSTRING;}else{$return_status[]=ISEMAIL_ERR_EXPECTING_ATEXT;}break;case ISEMAIL_STRING_CR:case ISEMAIL_STRING_SP:case ISEMAIL_STRING_HTAB:if(($token===ISEMAIL_STRING_CR)&&((++$i===$raw_length)||($email[$i]!==ISEMAIL_STRING_LF))){$return_status[]=ISEMAIL_ERR_CR_NO_LF;break;}if($element_len===0)$return_status[]=($element_count===0)?ISEMAIL_CFWS_FWS:ISEMAIL_DEPREC_FWS;else $end_or_die=true;$context_stack[]=$context;$context=ISEMAIL_CONTEXT_FWS;$token_prior=$token;break;case ISEMAIL_STRING_AT:if(count($context_stack)!==1)die('Unexpected item on context stack');if($parsedata[ISEMAIL_COMPONENT_LOCALPART]==='')$return_status[]=ISEMAIL_ERR_NOLOCALPART;elseif($element_len===0)$return_status[]=ISEMAIL_ERR_DOT_END;elseif(strlen($parsedata[ISEMAIL_COMPONENT_LOCALPART])>64)$return_status[]=ISEMAIL_RFC5322_LOCAL_TOOLONG;elseif(($context_prior===ISEMAIL_CONTEXT_COMMENT)||($context_prior===ISEMAIL_CONTEXT_FWS))$return_status[]=ISEMAIL_DEPREC_CFWS_NEAR_AT;$context=ISEMAIL_COMPONENT_DOMAIN;$context_stack=array($context);$element_count=0;$element_len=0;$end_or_die=false;break;default:if($end_or_die){switch($context_prior){case ISEMAIL_CONTEXT_COMMENT:case ISEMAIL_CONTEXT_FWS:$return_status[]=ISEMAIL_ERR_ATEXT_AFTER_CFWS;break;case ISEMAIL_CONTEXT_QUOTEDSTRING:$return_status[]=ISEMAIL_ERR_ATEXT_AFTER_QS;break;default:die("More atext found where none is allowed, but unrecognised prior context: $context_prior");}}else{$context_prior=$context;$ord=ord($token);if(($ord<33)||($ord>126)||($ord===10)||(!is_bool(strpos(ISEMAIL_STRING_SPECIALS,$token))))$return_status[]=ISEMAIL_ERR_EXPECTING_ATEXT;$parsedata[ISEMAIL_COMPONENT_LOCALPART].=$token;$atomlist[ISEMAIL_COMPONENT_LOCALPART][$element_count].=$token;$element_len++;}}break;case ISEMAIL_COMPONENT_DOMAIN:switch($token){case ISEMAIL_STRING_OPENPARENTHESIS:if($element_len===0)$return_status[]=($element_count===0)?ISEMAIL_DEPREC_CFWS_NEAR_AT:ISEMAIL_DEPREC_COMMENT;else{$return_status[]=ISEMAIL_CFWS_COMMENT;$end_or_die=true;}$context_stack[]=$context;$context=ISEMAIL_CONTEXT_COMMENT;break;case ISEMAIL_STRING_DOT:if($element_len===0)$return_status[]=($element_count===0)?ISEMAIL_ERR_DOT_START:ISEMAIL_ERR_CONSECUTIVEDOTS;elseif($hyphen_flag)$return_status[]=ISEMAIL_ERR_DOMAINHYPHENEND;else if($element_len>63)$return_status[]=ISEMAIL_RFC5322_LABEL_TOOLONG;$end_or_die=false;$element_len=0;$element_count++;$atomlist[ISEMAIL_COMPONENT_DOMAIN][$element_count]='';$parsedata[ISEMAIL_COMPONENT_DOMAIN].=$token;break;case ISEMAIL_STRING_OPENSQBRACKET:if($parsedata[ISEMAIL_COMPONENT_DOMAIN]===''){$end_or_die=true;$element_len++;$context_stack[]=$context;$context=ISEMAIL_COMPONENT_LITERAL;$parsedata[ISEMAIL_COMPONENT_DOMAIN].=$token;$atomlist[ISEMAIL_COMPONENT_DOMAIN][$element_count].=$token;$parsedata[ISEMAIL_COMPONENT_LITERAL]='';}else{$return_status[]=ISEMAIL_ERR_EXPECTING_ATEXT;}break;case ISEMAIL_STRING_CR:case ISEMAIL_STRING_SP:case ISEMAIL_STRING_HTAB:if(($token===ISEMAIL_STRING_CR)&&((++$i===$raw_length)||($email[$i]!==ISEMAIL_STRING_LF))){$return_status[]=ISEMAIL_ERR_CR_NO_LF;break;}if($element_len===0)$return_status[]=($element_count===0)?ISEMAIL_DEPREC_CFWS_NEAR_AT:ISEMAIL_DEPREC_FWS;else{$return_status[]=ISEMAIL_CFWS_FWS;$end_or_die=true;}$context_stack[]=$context;$context=ISEMAIL_CONTEXT_FWS;$token_prior=$token;break;default:if($end_or_die){switch($context_prior){case ISEMAIL_CONTEXT_COMMENT:case ISEMAIL_CONTEXT_FWS:$return_status[]=ISEMAIL_ERR_ATEXT_AFTER_CFWS;break;case ISEMAIL_COMPONENT_LITERAL:$return_status[]=ISEMAIL_ERR_ATEXT_AFTER_DOMLIT;break;default:die("More atext found where none is allowed, but unrecognised prior context: $context_prior");}}$ord=ord($token);$hyphen_flag=false;if(($ord<33)||($ord>126)||(!is_bool(strpos(ISEMAIL_STRING_SPECIALS,$token)))){$return_status[]=ISEMAIL_ERR_EXPECTING_ATEXT;}elseif($token===ISEMAIL_STRING_HYPHEN){if($element_len===0){$return_status[]=ISEMAIL_ERR_DOMAINHYPHENSTART;}$hyphen_flag=true;}elseif(!(($ord>47&&$ord<58)||($ord>64&&$ord<91)||($ord>96&&$ord<123))){$return_status[]=ISEMAIL_RFC5322_DOMAIN;}$parsedata[ISEMAIL_COMPONENT_DOMAIN].=$token;$atomlist[ISEMAIL_COMPONENT_DOMAIN][$element_count].=$token;$element_len++;}break;case ISEMAIL_COMPONENT_LITERAL:switch($token){case ISEMAIL_STRING_CLOSESQBRACKET:if((int)max($return_status)<ISEMAIL_DEPREC){$max_groups=8;$matchesIP=array();$index=false;$addressliteral=$parsedata[ISEMAIL_COMPONENT_LITERAL];if(preg_match('/\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/',$addressliteral,$matchesIP)>0){$index=strrpos($addressliteral,$matchesIP[0]);if($index!==0)$addressliteral=substr($addressliteral,0,$index).'0:0';}if($index===0){$return_status[]=ISEMAIL_RFC5321_ADDRESSLITERAL;}elseif(strncasecmp($addressliteral,ISEMAIL_STRING_IPV6TAG,5)!==0){$return_status[]=ISEMAIL_RFC5322_DOMAINLITERAL;}else{$IPv6=substr($addressliteral,5);$matchesIP=explode(ISEMAIL_STRING_COLON,$IPv6);$groupCount=count($matchesIP);$index=strpos($IPv6,ISEMAIL_STRING_DOUBLECOLON);if($index===false){if($groupCount!==$max_groups)$return_status[]=ISEMAIL_RFC5322_IPV6_GRPCOUNT;}else{if($index!==strrpos($IPv6,ISEMAIL_STRING_DOUBLECOLON))$return_status[]=ISEMAIL_RFC5322_IPV6_2X2XCOLON;else{if($index===0||$index===(strlen($IPv6)- 2))$max_groups++;if($groupCount>$max_groups)$return_status[]=ISEMAIL_RFC5322_IPV6_MAXGRPS;elseif($groupCount===$max_groups)$return_status[]=ISEMAIL_RFC5321_IPV6DEPRECATED;}}if((substr($IPv6,0,1)===ISEMAIL_STRING_COLON)&&(substr($IPv6,1,1)!==ISEMAIL_STRING_COLON))$return_status[]=ISEMAIL_RFC5322_IPV6_COLONSTRT;elseif((substr($IPv6,-1)===ISEMAIL_STRING_COLON)&&(substr($IPv6,-2,1)!==ISEMAIL_STRING_COLON))$return_status[]=ISEMAIL_RFC5322_IPV6_COLONEND;elseif(count(preg_grep('/^[0-9A-Fa-f]{0,4}$/',$matchesIP,PREG_GREP_INVERT))!==0)$return_status[]=ISEMAIL_RFC5322_IPV6_BADCHAR;else $return_status[]=ISEMAIL_RFC5321_ADDRESSLITERAL;}}else $return_status[]=ISEMAIL_RFC5322_DOMAINLITERAL;$parsedata[ISEMAIL_COMPONENT_DOMAIN].=$token;$atomlist[ISEMAIL_COMPONENT_DOMAIN][$element_count].=$token;$element_len++;$context_prior=$context;$context=(int)array_pop($context_stack);break;case ISEMAIL_STRING_BACKSLASH:$return_status[]=ISEMAIL_RFC5322_DOMLIT_OBSDTEXT;$context_stack[]=$context;$context=ISEMAIL_CONTEXT_QUOTEDPAIR;break;case ISEMAIL_STRING_CR:case ISEMAIL_STRING_SP:case ISEMAIL_STRING_HTAB:if(($token===ISEMAIL_STRING_CR)&&((++$i===$raw_length)||($email[$i]!==ISEMAIL_STRING_LF))){$return_status[]=ISEMAIL_ERR_CR_NO_LF;break;}$return_status[]=ISEMAIL_CFWS_FWS;$context_stack[]=$context;$context=ISEMAIL_CONTEXT_FWS;$token_prior=$token;break;default:$ord=ord($token);if(($ord>127)||($ord===0)||($token===ISEMAIL_STRING_OPENSQBRACKET)){$return_status[]=ISEMAIL_ERR_EXPECTING_DTEXT;break;}elseif(($ord<33)||($ord===127)){$return_status[]=ISEMAIL_RFC5322_DOMLIT_OBSDTEXT;}$parsedata[ISEMAIL_COMPONENT_LITERAL].=$token;$parsedata[ISEMAIL_COMPONENT_DOMAIN].=$token;$atomlist[ISEMAIL_COMPONENT_DOMAIN][$element_count].=$token;$element_len++;}break;case ISEMAIL_CONTEXT_QUOTEDSTRING:switch($token){case ISEMAIL_STRING_BACKSLASH:$context_stack[]=$context;$context=ISEMAIL_CONTEXT_QUOTEDPAIR;break;case ISEMAIL_STRING_CR:case ISEMAIL_STRING_HTAB:if(($token===ISEMAIL_STRING_CR)&&((++$i===$raw_length)||($email[$i]!==ISEMAIL_STRING_LF))){$return_status[]=ISEMAIL_ERR_CR_NO_LF;break;}$parsedata[ISEMAIL_COMPONENT_LOCALPART].=ISEMAIL_STRING_SP;$atomlist[ISEMAIL_COMPONENT_LOCALPART][$element_count].=ISEMAIL_STRING_SP;$element_len++;$return_status[]=ISEMAIL_CFWS_FWS;$context_stack[]=$context;$context=ISEMAIL_CONTEXT_FWS;$token_prior=$token;break;case ISEMAIL_STRING_DQUOTE:$parsedata[ISEMAIL_COMPONENT_LOCALPART].=$token;$atomlist[ISEMAIL_COMPONENT_LOCALPART][$element_count].=$token;$element_len++;$context_prior=$context;$context=(int)array_pop($context_stack);break;default:$ord=ord($token);if(($ord>127)||($ord===0)||($ord===10)){$return_status[]=ISEMAIL_ERR_EXPECTING_QTEXT;}elseif(($ord<32)||($ord===127))$return_status[]=ISEMAIL_DEPREC_QTEXT;$parsedata[ISEMAIL_COMPONENT_LOCALPART].=$token;$atomlist[ISEMAIL_COMPONENT_LOCALPART][$element_count].=$token;$element_len++;}break;case ISEMAIL_CONTEXT_QUOTEDPAIR:$ord=ord($token);if($ord>127)$return_status[]=ISEMAIL_ERR_EXPECTING_QPAIR;elseif((($ord<31)&&($ord!==9))||($ord===127))$return_status[]=ISEMAIL_DEPREC_QP;$context_prior=$context;$context=(int)array_pop($context_stack);$token=ISEMAIL_STRING_BACKSLASH.$token;switch($context){case ISEMAIL_CONTEXT_COMMENT:break;case ISEMAIL_CONTEXT_QUOTEDSTRING:$parsedata[ISEMAIL_COMPONENT_LOCALPART].=$token;$atomlist[ISEMAIL_COMPONENT_LOCALPART][$element_count].=$token;$element_len+=2;break;case ISEMAIL_COMPONENT_LITERAL:$parsedata[ISEMAIL_COMPONENT_DOMAIN].=$token;$atomlist[ISEMAIL_COMPONENT_DOMAIN][$element_count].=$token;$element_len+=2;break;default:die("Quoted pair logic invoked in an invalid context: $context");}break;case ISEMAIL_CONTEXT_COMMENT:switch($token){case ISEMAIL_STRING_OPENPARENTHESIS:$context_stack[]=$context;$context=ISEMAIL_CONTEXT_COMMENT;break;case ISEMAIL_STRING_CLOSEPARENTHESIS:$context_prior=$context;$context=(int)array_pop($context_stack);break;case ISEMAIL_STRING_BACKSLASH:$context_stack[]=$context;$context=ISEMAIL_CONTEXT_QUOTEDPAIR;break;case ISEMAIL_STRING_CR:case ISEMAIL_STRING_SP:case ISEMAIL_STRING_HTAB:if(($token===ISEMAIL_STRING_CR)&&((++$i===$raw_length)||($email[$i]!==ISEMAIL_STRING_LF))){$return_status[]=ISEMAIL_ERR_CR_NO_LF;break;}$return_status[]=ISEMAIL_CFWS_FWS;$context_stack[]=$context;$context=ISEMAIL_CONTEXT_FWS;$token_prior=$token;break;default:$ord=ord($token);if(($ord>127)||($ord===0)||($ord===10)){$return_status[]=ISEMAIL_ERR_EXPECTING_CTEXT;break;}elseif(($ord<32)||($ord===127)){$return_status[]=ISEMAIL_DEPREC_CTEXT;}}break;case ISEMAIL_CONTEXT_FWS:if($token_prior===ISEMAIL_STRING_CR){if($token===ISEMAIL_STRING_CR){$return_status[]=ISEMAIL_ERR_FWS_CRLF_X2;break;}if(isset($crlf_count)){if(++$crlf_count>1)$return_status[]=ISEMAIL_DEPREC_FWS;}else $crlf_count=1;}switch($token){case ISEMAIL_STRING_CR:if((++$i===$raw_length)||($email[$i]!==ISEMAIL_STRING_LF))$return_status[]=ISEMAIL_ERR_CR_NO_LF;break;case ISEMAIL_STRING_SP:case ISEMAIL_STRING_HTAB:break;default:if($token_prior===ISEMAIL_STRING_CR){$return_status[]=ISEMAIL_ERR_FWS_CRLF_END;break;}if(isset($crlf_count))unset($crlf_count);$context_prior=$context;$context=(int)array_pop($context_stack);$i--;}$token_prior=$token;break;default:die("Unknown context: $context");}if((int)max($return_status)>ISEMAIL_RFC5322)break;}if((int)max($return_status)<ISEMAIL_RFC5322){if($context===ISEMAIL_CONTEXT_QUOTEDSTRING)$return_status[]=ISEMAIL_ERR_UNCLOSEDQUOTEDSTR;elseif($context===ISEMAIL_CONTEXT_QUOTEDPAIR)$return_status[]=ISEMAIL_ERR_BACKSLASHEND;elseif($context===ISEMAIL_CONTEXT_COMMENT)$return_status[]=ISEMAIL_ERR_UNCLOSEDCOMMENT;elseif($context===ISEMAIL_COMPONENT_LITERAL)$return_status[]=ISEMAIL_ERR_UNCLOSEDDOMLIT;elseif($token===ISEMAIL_STRING_CR)$return_status[]=ISEMAIL_ERR_FWS_CRLF_END;elseif($parsedata[ISEMAIL_COMPONENT_DOMAIN]==='')$return_status[]=ISEMAIL_ERR_NODOMAIN;elseif($element_len===0)$return_status[]=ISEMAIL_ERR_DOT_END;elseif($hyphen_flag)$return_status[]=ISEMAIL_ERR_DOMAINHYPHENEND;elseif(strlen($parsedata[ISEMAIL_COMPONENT_DOMAIN])>255)$return_status[]=ISEMAIL_RFC5322_DOMAIN_TOOLONG;elseif(strlen($parsedata[ISEMAIL_COMPONENT_LOCALPART].ISEMAIL_STRING_AT.$parsedata[ISEMAIL_COMPONENT_DOMAIN])>254)$return_status[]=ISEMAIL_RFC5322_TOOLONG;elseif($element_len>63)$return_status[]=ISEMAIL_RFC5322_LABEL_TOOLONG;}$dns_checked=false;if($checkDNS&&((int)max($return_status)<ISEMAIL_DNSWARN)&&function_exists('dns_get_record')){if($element_count===0)$parsedata[ISEMAIL_COMPONENT_DOMAIN].='.';$result=@dns_get_record($parsedata[ISEMAIL_COMPONENT_DOMAIN],DNS_MX);if((is_bool($result)&&!(bool)$result))$return_status[]=ISEMAIL_DNSWARN_NO_RECORD;else{if(count($result)===0){$return_status[]=ISEMAIL_DNSWARN_NO_MX_RECORD;$result=@dns_get_record($parsedata[ISEMAIL_COMPONENT_DOMAIN],DNS_A + DNS_CNAME);if(count($result)===0)$return_status[]=ISEMAIL_DNSWARN_NO_RECORD;}else $dns_checked=true;}}if(!$dns_checked&&((int)max($return_status)<ISEMAIL_DNSWARN)){if($element_count===0)$return_status[]=ISEMAIL_RFC5321_TLD;if(is_numeric($atomlist[ISEMAIL_COMPONENT_DOMAIN][$element_count][0]))$return_status[]=ISEMAIL_RFC5321_TLDNUMERIC;}$return_status=array_unique($return_status);$final_status=(int)max($return_status);if(count($return_status)!==1)array_shift($return_status);$parsedata['status']=$return_status;if($final_status<$threshold)$final_status=ISEMAIL_VALID;return($diagnose)?$final_status:($final_status<ISEMAIL_THRESHOLD);}?>