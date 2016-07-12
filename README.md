malware-repo
============

Malware Repository Framework
Official page: http://www.adlice.com/download/mrf

## Version 4.0
New UI, based on AdminLTE  
Using more recent versions of bootstrap and Jquery  

## Version 3.4
Cuckoo: Now you can rescan files  
Cuckoo: Fixed filename (useful for package selection)  
VirusTotal: Fixed filename  
Cuckoo: Added scan parameters in config file  
Fixed a bug preventing comment to be stored  
Fixed VirusTotal uploads with PHP 5.6+  
Fixed Cuckoo uploads with PHP 5.6+  

## Version 3.3
Added URLs to API  
Moved sample comment in meta table (!Breaks backward compatibility!)  
Cuckoo: now storing only database ID instead so that all links are dynamic (!Breaks backward compatibility!)  
Cuckoo: removed unused report field (!Breaks backward compatibility!)  
Cuckoo: compatible with version 2  
Cuckoo: Now able to retrieve and reference old sample reports  

## Version 3.2
Added EULA  
Added cron for VirusTotal and Cuckoo status refresh  
Added URLs sample information  
Added ability to send comment on VirusTotal  
Better tags search and storage  
Added ZIp extraction (no password for now)  
Now comment is displayed/modified into a modal dialog (this allows big comments)  

## Version 3.1
UI fixes  
UI improvements  
Added tags  
Added favorites  
Added more data collapsable row  
Moved some fields into collapsed row  
fixed a lot of bugs  

## Version 3.0
Code reorganization, with now only one config file to change  
Added installer script  
Moved filters into a search tab  
UI tweaks and improvements  

## Version 2.0
Yes, there's no version 1 :)  
Added REST API, even for the UI  
Added Authentication with UserCake. Every user has an API key.  
User can only delete/edit its own samples, unless the user is admin.  
Ability to send samples with REST API, an API key is needed.  
Now samples keep the uploader in database.  
Now samples have editable comment field. Comment can also be sent via API.  
Fixed a lot of bugs.  
Improved UI.  
Added ability to NOT automatically upload to VirusTotal  
Now deployment is easy with the install script  

## Version 0.4
Cuckoo reports are now saved on disk, locally. So that you don't need your cuckoo machine to be up and running to view a report.  
All queries are now properly escaped.  
Added VT score filter.  

## Version 0.3
Added VT re-scan button  
Added Cuckoo support, and cuckoo scan button + results  
Added pagination  
Fixed bugs  

## Version 0.2 
Added Edit button, can change vendor name  
Fixed VT scan when file is unknown  
Now files uploaded are shown first  

## Version 0.1
Initial release  
