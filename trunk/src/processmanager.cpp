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

#include <sstream>
#include <fstream>

#include "processmanager.h"
#include "tools.h"
#include "debug.h"
#include "logger.h"

ProcessManager::ProcessManager ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  _started = false;
}


ProcessManager::~ProcessManager ()
{
  DEBUG (DEBUG7, "[" << this << "->" << __FUNCTION__ << "]");

  stop ();
}

/**
 * @todo Использовать директорию для создания файла с номером процесса в соответствии с опциями configure
 */
bool ProcessManager::start (const string & procname, bool wait_myself)
{
  if (_started)
    {
      WARNING ("Started already.");
      return false;
    }

  fstream f;
  pid_t pid;

  _fname = "/var/run/" + procname + ".pid";

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

      bool is_runing = (kill (pid, 0) == 0);
      if (is_runing && !wait_myself)
        {
          ERROR ("Already running with pid " << pid);
          return false;
        }
      else if (is_runing && wait_myself)
        {
          while (is_runing)
            {
              sleep(2);
              is_runing = (kill (pid, 0) == 0);
            }
        }
      else
        {
          WARNING ("Pid file exists, but no program running. Unexpected crash?");
        }
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

  basic_stringstream < char >mess;

  mess << "Started with pid " << pid << ".";

  Logger::addLog(Logger::LK_DAEMON, mess.str());

  DEBUG (DEBUG_FILE, _fname << " created.");
  _started = true;

  return true;
}

pid_t ProcessManager::isRunning (const string & procname)
{
  fstream f;
  string pid_fname;
  pid_t pid = 0;
  bool is_runing = false;

  pid_fname = "/var/run/" + procname + ".pid";

  if (fileExist (pid_fname))
    {
      f.open (pid_fname.c_str (), ios_base::in);
      if (!f.is_open ())
        {
          ERROR ("Failed to open file " << pid_fname);
          return 0;
        }
      f >> pid;
      f.close ();

      is_runing = (kill (pid, 0) == 0);

      if (!is_runing)
        {
          pid = 0;
          WARNING ("Pid file exists, but no program running. Unexpected crash?");
        }
    }
  return pid;
}

void ProcessManager::stop ()
{
  if (!_started)
      return;

  Logger::addLog(Logger::LK_DAEMON, "Stopped.");
  fileDelete (_fname);
  _started = false;
}
