<?php
    $hostname = "";
    $port = ;
    
    // Attribute use to identify user on LDAP	
    $search_attribute = "";
   
    $base_dn = "";
    $filter = "";
    
    // ldap service user to allow search in ldap
    $bind_dn = "";
    $bind_pass = "";

    $ldap_config = array("hostname"=>$hostname, "port"=>$port, "search_attribute"=>$search_attribute, "base_dn"=>$base_dn, "filter"=>$filter, "bind_dn"=>$bind_dn, "bind_pass"=>$bind_pass);
    return $ldap_config;
?>
