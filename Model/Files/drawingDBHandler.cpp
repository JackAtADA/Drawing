#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <iostream>
#include <string>
#include <Windows.h>
#include <WinBase.h>
#include <Winreg.h>
#include <direct.h>

using namespace std;

int CreateRegisterFile();
int CreateConnection(const char * protocol );
int ParsePaths(const char * protocol, string & serverPath, string & filePath);

int main(int argc, char * argv[]){
    printf("argc = %d args =",argc);
    int i = 0;
    for (i = 0;i<argc;i++){
        printf("\t'%s'",argv[i]);
    }
    printf("\n");
    
    if (argc > 1){
        CreateConnection(argv[1]);
    }else{
        CreateRegisterFile();
    }
    system("pause");
    return 0;
}

int ParsePaths(const char * protocol, string & serverPath, string & filePath){
	// input the protocol and the function will output the server path and file path
	// ex protocal is a string(a real string, not a data type in c++) 
	//	"drawingdb://server=\\168.8.204.99\e&m\&file=Work Order\ViP Building\test.txt"
	// return value:
	//	0: parse failed
	//	1: parse successful, check the output value in variable string serverPath, string filePath
	
	char * serverKey= "drawingdb://server=";
	char * fileKey = "\\&file=";
	
	const char * serverEndPos = strstr(protocol, fileKey); 
	// the next char of ending, should be "\"
	// the "\" can't be include to server path, it will cause the command "net use" 
	// can't find the server driver
	if ( serverEndPos == NULL || strstr(protocol, serverKey) != protocol){
		return 0;
	}
	const char * serverStartPos = protocol + strlen(serverKey);
	size_t serverPathLen = serverEndPos - serverStartPos;
	
	serverPath.assign(serverStartPos, serverPathLen);
	
	const char * fileEndPos = protocol + strlen(protocol); // the next char of ending, should be null
	const char * fileStartPos = strstr(protocol, fileKey) + strlen(fileKey);
	size_t filePathLen = fileEndPos - fileStartPos;
	
	if (protocol[strlen(protocol) - 1] == '/'){ // the len of protocol won't be 0
		filePathLen -= 1; // skip the last slash
	}
	filePath.assign(fileStartPos, filePathLen);
	return 1;
}


int CreateConnection(const char * protocol ){
	// the function will parse protocal and use windowns cmd as intermedia to create
	// connection to the network drive in window OS.
	// When the connection is created, it will also open the directly the explorer to
	// the target file.
	
	// return value,
	//	0, error
	//	1, create connection successful. but this does not guarantee the target file
	//		can be located
	
	string serverPath;
	string filePath;
	int ret = ParsePaths(protocol, serverPath, filePath);
	if (!ret){
		cerr << "protocol format error:" << protocol << endl;
		return 0;
	}
	//cout << "server " << serverPath << " file " << filePath << endl;
	
	if (filePath.length() + serverPath.length() > 2048){
		cerr << "path is too long" << endl;
		return 0;
	}
	
	string command("net use \"");
	command.append(serverPath);
	command.append("\"");
	
    cout << "Process cmd:" << command << endl;
    int status = system(command.c_str());
	if (status != 0){
		// network connect fail;
		return 0;
	}
	
	command.assign("explorer /select,\"");
	command.append(serverPath);
	command.append("\\");
	command.append(filePath);
	command.append("\"");
	cout << "Process cmd:" << command << endl;
	status = system(command.c_str());
	// status should be always 0, because explorer will not check the file is existed or not.
	if (status !=0){
		return 0;
	}
	return 1;
}

int CreateRegisterFile(){
	//wchar_t cwd[1024];
	char cwd[1024];
	//wchar_t *ret = _wgetcwd( cwd, 1024 );
	char *ret = _getcwd( cwd, 1024 );
	
	if (ret == NULL){
		return 0;
	}
	//wstring path(cwd);
	string path(cwd);
	//path.append(L"\\drawingDBHandler.reg");
	path.append("\\drawingDBHandler.reg");
	
    FILE *fp;
	//fp = _wfopen(path.c_str(), L"w,ccs=UTF-8");
	fp = fopen(path.c_str(), "w");
	if (fp == NULL){
		cerr << "can't not create register file in current location" << endl;
	}
	
	string content = "Windows Registry Editor Version 5.00\n\n";
	content.append("[HKEY_CLASSES_ROOT\\drawingdb]\n");
	content.append("\"URL Protocol\"=\"\"\n");
	content.append("@=\"\\\"URL:drawingdb Protocol\\\"\"\n\n");
	content.append("[HKEY_CLASSES_ROOT\\drawingdb\\DefaultIcon]\n");
	content.append("@=\"\\\"DrawingDBHandler.exe,1\\\"\"\n\n");
	content.append("[HKEY_CLASSES_ROOT\\drawingdb\\shell]\n\n");
	content.append("[HKEY_CLASSES_ROOT\\drawingdb\\shell\\open]\n\n");
	content.append("[HKEY_CLASSES_ROOT\\drawingdb\\shell\\open\\command]\n");
	content.append("@=\"\\\"");
	fwrite(content.c_str(), sizeof(char), content.length(), fp);
	
    path.assign(cwd);
	path.append("\\drawingDBHandler.exe");
	const char *index = path.c_str();
	while(*index !='\0'){
		fwrite(index, sizeof(char), 1, fp);
		if ( *index == '\\' ){
			fwrite(index, sizeof(char), 1, fp);
		}
		index++;
    }
    content.assign("\\\" \\\"\%1\\\"\"");
    fwrite(content.c_str(), sizeof(char), content.length(), fp);
	/*
	path.assign(cwd);
	path.append(L"\\drawingDBHandler.exe");
	const wchar_t *index = path.c_str();
	wchar_t tmp[1024];
	char utf8Buffer[1024];
	while(*index != L'\0'){
		tmp[0] = index[0];  // copy the character to tmp
		tmp[1] = L'\0';		// terminate with NULL
		WideCharToMultiByte (CP_UTF8,0,tmp,-1,utf8Buffer,1024,NULL,NULL);
		fwrite(utf8Buffer, sizeof(char), strlen(utf8Buffer), fp);
		if ( *index == L'\\' ){
			tmp[0] = L'\\';
			WideCharToMultiByte (CP_UTF8,0,tmp,-1,utf8Buffer,1024,NULL,NULL);
			fwrite(utf8Buffer, sizeof(char), strlen(utf8Buffer), fp);
		}
		index++;
    }
    content.assign("\\\" \\\"\%1\\\"\"");
    fwrite(content.c_str(), sizeof(char), content.length(), fp);
        
    //@="\"C:\\Program Files\\DrawingDBHandler\\drawingDBHandler.exe\" \"%1\""
	*/
	fclose(fp);
	system("Reg import drawingDBHandler.reg");

    return 1;
}
