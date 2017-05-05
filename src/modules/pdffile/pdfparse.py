#!/usr/bin/env python
import os, sys, json, string
from pdfminer.pdfparser import PDFParser
from pdfminer.pdfdocument import PDFDocument
from pdfminer.pdftypes import PDFStream, dict_value, list_value #pip install -U pdfminer
from pdfminer.pdftypes import PDFObjRef
from pdfminer.psparser import PSLiteral

def parse_args():
    global pathspec
    if (len(sys.argv) == 1) | (len(sys.argv) > 7):
        usage()
        quit(1)
    
    ignore_next = False
    for arg in sys.argv[1:]:
        if ignore_next:
            ignore_next = False
            continue
        
        if arg == '-?' or arg == '/?':
            usage()
            exit(1)
            
        # some unknown input
        elif arg[0] == '-':
            print '################################\n'
            print '{0} is not a valid argument!\n'.format(arg)
            print '################################\n'
            usage()
            exit(1) 
        else:
            if pathspec == None:
                pathspec = os.path.normpath(arg)

def usage():
    print """pdfparse: parse pdf output for a file, extract metadata
    
usage: pdfparse <pathspec> [-?]
    
where:
    <pathspec>    file to scan.
    -?            Show this usage screen"""

def convert_char(char):
    if char in string.ascii_letters or char in string.digits or char in string.punctuation or char in string.whitespace:
        return char            
    else:
        return '?'
  
def convert_to_printable_null_terminated(s):
    str_list = []
    if (s is None):
        return ''.join(str_list)
    for c in s:
        if (c=='\0'):   #null byte is here to mark the end of the string
            str_list.append(c)
            break;
        else:
            str_list.append(convert_char(c))
    
    return ''.join(str_list)

def convert_to_printable(obj):
    if isinstance(obj, str):
        return convert_to_printable_null_terminated(obj)
    
    if isinstance(obj, int):
        return str(obj)
    
    if isinstance(obj, float):
        return str(obj)
        
    if isinstance(obj, list):
        new_obj = []
        for _, value in enumerate(obj):
            new_obj.append(convert_to_printable(value))
        return new_obj
        
    if isinstance(obj, dict):
        new_obj = {}
        for key, value in obj.iteritems():
            new_obj[key] = convert_to_printable(value)
        return new_obj
    
    if isinstance(obj, PDFObjRef):
        return obj.objid
    
    if isinstance(obj, PSLiteral):
        return obj.name
    
    return "<error: not parsed>"

def ProcessFile(path):
    if not(os.path.isfile(path)):
        print '{0} not a file!'.format(path)
        return 2

    try:
        data = {}
        data['valid'] = True  
        pdfdata = {}
        
        infile  = file(path, 'rb')
        parser  = PDFParser(infile)
        doc     = PDFDocument(parser)
        encryption = None
        info    = []
        root    = None
        
        # Enumerate trailers, only 1 root allowed
        for xref in doc.xrefs:
            trailer = xref.get_trailer()
            if not trailer:
                continue
            
            # If there's an encryption info, remember it.
            if 'Encrypt' in trailer:
                #encryption = (list_value(trailer['ID']), dict_value(trailer['Encrypt']))
                pass
            if 'Info' in trailer:
                pass
            if 'Root' in trailer:
                #  Every PDF file must have exactly one /Root dictionary.
                root         = xref
                data['trailer'] = convert_to_printable(trailer)
                break 
             
        if not root:
            data['valid'] = False
        else:      
            errors  = []
            streams = []  
            # enumerate streams inside root
            for objid in root.get_objids():
                try:                    
                    obj = doc.getobj(objid)
                    if not obj:
                        continue                    
                    
                    stream = {}
                    stream['id'] = objid
                    
                    if isinstance(obj, str):
                        stream['attributes'] = [ convert_to_printable(obj) ]
                    
                    if isinstance(obj, list):
                        stream['attributes'] = convert_to_printable(obj)
                        
                    if isinstance(obj, dict):
                        stream['attributes'] = convert_to_printable(obj)
                    
                    if isinstance(obj, PDFStream):
                        stream['attributes'] = convert_to_printable(obj.attrs)
                        try:
                            stream_data         = obj.get_data()
                            stream['data']      = convert_to_printable(stream_data)
                            stream['data_len']  = len(stream_data)
                        except Exception as e:
                            errors.append(str(e))
        
                    streams.append(stream)
        
                except Exception as e:
                    errors.append(str(e))
                    
            pdfdata['streams']  = streams
            pdfdata['errors']   = errors          
                
        # close
        parser.close()
        infile.close()
        
        data['data'] = pdfdata
        encoded = json.dumps(data)
        print encoded
    except Exception as ex:
        data = {}
        data['valid'] = False
        data['error'] = str(ex)
        print json.dumps(data)
        return 1
        
    return 0

#--------------------------------------------------------------------------------------------------
#--------------------------------------------------------------------------------------------------
#--------------------------------------------------------------------------------------------------
cwd = os.path.dirname(os.path.realpath(__file__))
pathspec = None

parse_args()

# validate path input
if (pathspec == None):
    print('A path specification is required')
    exit(2)

# convert relative path to absolute path
if len(os.path.splitdrive(pathspec)[0]) == 0:
    pathspec = os.path.normpath(os.path.join(cwd, pathspec))

if os.path.isdir(pathspec):
    print 'please specify a file arg'
    exit(2)
else:
    exit(ProcessFile(pathspec))

