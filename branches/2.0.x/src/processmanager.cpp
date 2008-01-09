/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
#include <sys/types.h>
#include <unistd.h>
#include <signal.h>

#include <fstream>

#include "processmanager.h"
#include "tools.h"
#include "debug.h"

ProcessManager::ProcessManager ()
{
}


ProcessManager::~ProcessManager ()
{
  stop ();
}

/**
 * @todo Использовать директорию для создания файла с номером процесса в соответствии с опциями configure
 */
bool ProcessManager::start (const string & procname)
{
  fstream f;
  pid_t pid;

  _fname = "/tmp/" + procname + ".pid";

  if (fileExist (_fname))
    {
      f.open (_fname.c_str (), ios_base::in);
      if (!f.is_open ())
        {
          ERROR ("Failed to open file " << _fname);
          return false;
        }
      f >> pid;
      f.close ();

      if (kill (pid, 0) == 0)
        {
          ERROR ("Already running with pid " << pid);
          return false;
        }

      WARNING ("Pid file exists, but no program running. Unexpected crash?");
    }

  pid = getpid ();
  f.open (_fname.c_str (), ios_base::out);
  if (!f.is_open ())
    {
      ERROR ("Failed to open file " << _fname);
      return false;
    }
  f << pid;
  f.close ();

  DEBUG (DEBUG_FILE, _fname << " created.");
  return true;
}


void ProcessManager::stop ()
{
  fileDelete (_fname);
}
