del /q *.o *.exe *.i
gcc -c chitu-common.c chitu-bool.c chitu-float.c chitu-int.c chitu-string.c
gcc -c test.c
gcc test.o chitu-common.o chitu-bool.o chitu-float.o chitu-int.o chitu-string.o -o test.exe
test.exe
