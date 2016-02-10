malware-repo
============

Malware Repository Framework
Official page: http://www.adlice.com/softwares/malware-repository-framework/

## Version 3.2
Added EULA<br/>
Added cron for VirusTotal and Cuckoo status refresh<br/>
Added URLs sample information<br/>
Added ability to send comment on VirusTotal<br/>
Better tags search and storage<br/>
Added ZIp extraction (no password for now)<br/>
Now comment is displayed/modified into a modal dialog (this allows big comments)<br/>

## Version 3.1
UI fixes<br/>
UI improvements<br/>
Added tags<br/>
Added favorites<br/>
Added more data collapsable row<br/>
Moved some fields into collapsed row<br/>
fixed a lot of bugs<br/>

## Version 3.0
Code reorganization, with now only one config file to change<br/>
Added installer script<br/>
Moved filters into a search tab<br/>
UI tweaks and improvements<br/>

## Version 2.0
Yes, there's no version 1 :)<br/>
Added REST API, even for the UI<br/>
Added Authentication with UserCake. Every user has an API key.<br/>
User can only delete/edit its own samples, unless the user is admin.<br/>
Ability to send samples with REST API, an API key is needed.<br/>
Now samples keep the uploader in database.<br/>
Now samples have editable comment field. Comment can also be sent via API.<br/>
Fixed a lot of bugs.<br/>
Improved UI.<br/>
Added ability to NOT automatically upload to VirusTotal<br/>
Now deployment is easy with the install script<br/>

## Version 0.4
Cuckoo reports are now saved on disk, locally. So that you don't need your cuckoo machine to be up and running to view a report.<br/>
All queries are now properly escaped.<br/>
Added VT score filter.<br/>

## Version 0.3
Added VT re-scan button<br/>
Added Cuckoo support, and cuckoo scan button + results<br/>
Added pagination<br/>
Fixed bugs<br/>

## Version 0.2 
Added Edit button, can change vendor name<br/>
Fixed VT scan when file is unknown<br/>
Now files uploaded are shown first<br/>

## Version 0.1
Initial release<br/>
