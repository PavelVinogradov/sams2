PERSERVESTATE = 1
USETEXTLINKS = 1
STARTALLOPEN = 0
ICONPATH = 'white_data/'

foldersTree = gFld("SQUID", "title.html", "earth.gif")
   redir = insFld(foldersTree, gFld("���������������", "main.php?show=addredirectform", "redirect.gif"))
      insDoc(redir, gLnk("R", "�������", "main.php?show=editredirectform&groupname=banners","pfile.gif"))
      insDoc(redir, gLnk("R", "��������", "main.php?show=editredirectform&groupname=counters","pfile.gif"))
   denied = insFld(foldersTree, gFld("������ �������", "main.php?show=adddeniedform", "stop.gif"))
      insDoc(denied, gLnk("R", "����", "main.php?show=editredirectform&groupname=chat","pfile.gif"))
      insDoc(denied, gLnk("R", "����������", "main.php?show=editredirectform&groupname=porno","pfile.gif"))
   groups = insFld(foldersTree, gFld("������ �������������", "main.php?show=newgroupform", "paddressbook.gif"))
      insDoc(groups, gLnk("R", "������������", "main.php?show=deletegroupform&groupname=user","pgroup.gif"))
      insDoc(groups, gLnk("R", "��������������", "main.php?show=deletegroupform&groupname=admin","pgroup.gif"))
      insDoc(groups, gLnk("R", "������", "main.php?show=deletegroupform&groupname=0tmp0GaMnrT","pgroup.gif"))
   users = insFld(foldersTree, gFld("������������", "main.php?show=newuserform", "paddressbook.gif"))
     users0 = insFld(users, gFld("������������", "main.php?show=usergroupform&groupname=user&groupnick=������������", "pgroup.gif"))
        insDoc(users0, gLnk("R", "chemerik", "main.php?show=userform&usernick=chemerik&userfamily=�������&usergroup=user","puser.gif"))
        insDoc(users0, gLnk("R", "extensa", "main.php?show=userform&usernick=extensa&userfamily=����-������&usergroup=user","puser.gif"))
     users0 = insFld(users, gFld("��������������", "main.php?show=usergroupform&groupname=admin&groupnick=��������������", "pgroup.gif"))
     users0 = insFld(users, gFld("������", "main.php?show=usergroupform&groupname=0tmp0GaMnrT&groupnick=������", "pgroup.gif"))
        insDoc(users0, gLnk("R", "Petrov", "main.php?show=userform&usernick=Petrov&userfamily=&usergroup=0tmp0GaMnrT","puser.gif"))
        insDoc(users0, gLnk("R", "Ivanov", "main.php?show=userform&usernick=Ivanov&userfamily=&usergroup=0tmp0GaMnrT","puser.gif"))
        insDoc(users0, gLnk("R", "Guest", "main.php?show=userform&usernick=Guest&userfamily=&usergroup=0tmp0GaMnrT","puser.gif"))
   licenses = insDoc(foldersTree,gLnk("R","���������� ������������","licenses.html","floppy.gif"))
   licenses = insDoc(foldersTree,gLnk("R","������������������ SQUID","licenses.html","pobject.gif"))
