#include <stdio.h>
#include <wtypes.h>
#include <wincon.h>
 
HANDLE hconin = INVALID_HANDLE_VALUE;
DWORD cmode;
 
void restore_term(void) {
	if (hconin == INVALID_HANDLE_VALUE)
		return;
 
	SetConsoleMode(hconin, cmode);
	CloseHandle(hconin);
	hconin = INVALID_HANDLE_VALUE;
}
 
int disable_echo(void) {
	hconin = CreateFile("CONIN$", GENERIC_READ | GENERIC_WRITE,
	FILE_SHARE_READ, NULL, OPEN_EXISTING,
	FILE_ATTRIBUTE_NORMAL, NULL);
	if (hconin == INVALID_HANDLE_VALUE)
		return -1;
 
	GetConsoleMode(hconin, &cmode);
	if (!SetConsoleMode(hconin, cmode & (~ENABLE_ECHO_INPUT))) {
		CloseHandle(hconin);
		hconin = INVALID_HANDLE_VALUE;
		return -1;
	}
 
	return 0;
}
 
int main(void) {
	char psw[100];
 
	disable_echo();
	fgets(psw, 100, stdin);
	restore_term();
	printf("%s", psw);
 
	return 0;
}