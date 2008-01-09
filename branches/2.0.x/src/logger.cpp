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
#include <syslog.h>
#include <iostream>

#include "logger.h"
#include "tools.h"

Logger::Logger ()
{
  _engine = OUT_CONSOLE;
  _started = false;
  _dbgLevel = 0;
  _verbose = false;
}


Logger::~Logger ()
{
  stop ();
}


void Logger::sendInfo (const string & mess)
{
  if (!_verbose)
    return;

  switch (_engine)
    {
    case OUT_CONSOLE:
      cout << mess << endl;
      break;
    case OUT_FILE:
      _fout << mess << endl;
      break;
    case OUT_SYSLOG:
      syslog (LOG_INFO, "%s", mess.c_str ());
      break;
    default:
      break;
    }
}


void Logger::sendDebug (uint level, const string & mess)
{
  if ((_dbgLevel == 0) || (_dbgLevel < level))
    return;

  switch (_engine)
    {
    case OUT_CONSOLE:
      cout << mess << endl;
      break;
    case OUT_FILE:
      _fout << mess << endl;
      break;
    case OUT_SYSLOG:
      syslog (LOG_DEBUG, "%s", mess.c_str ());
      break;
    default:
      break;
    }
}


void Logger::sendWarning (const string & mess)
{
  switch (_engine)
    {
    case OUT_CONSOLE:
      cerr << mess << endl;
      break;
    case OUT_FILE:
      _fout << mess << endl;
      break;
    case OUT_SYSLOG:
      syslog (LOG_WARNING, "%s", mess.c_str ());
      break;
    default:
      break;
    }
}


void Logger::sendError (const string & mess)
{
  switch (_engine)
    {
    case OUT_CONSOLE:
      cerr << mess << endl;
      break;
    case OUT_FILE:
      _fout << mess << endl;
      break;
    case OUT_SYSLOG:
      syslog (LOG_ERR, "%s", mess.c_str ());
      break;
    default:
      break;
    }
}


bool Logger::setEngine (const string & engine)
{
  stop ();

  vector < string > tblOptions;

  if (engine == "syslog")
    {
      openlog ("samsparser", LOG_PID | LOG_CONS, LOG_DAEMON);
      _engine = OUT_SYSLOG;
      _started = true;
    }
  else if (engine == "console")
    {
      _engine = OUT_CONSOLE;
      _started = true;
    }
  else if (engine.find ("file") >= 0)
    {
      string fname;

      Split (engine, ":", tblOptions);
      if (tblOptions.size () == 2)
        fname = tblOptions[1];
      else
        fname = "samsparser.log";

      _fout.open (fname.c_str (), ios::out);

      _started = true;

      if (!_fout.is_open ())
        {
          _engine = OUT_CONSOLE;
          sendError ("Unable to open file " + fname);
        }
      else
        {
          _engine = OUT_FILE;
        }
    }

  return _started;
}


void Logger::setDebugLevel (uint level)
{
  _dbgLevel = level;
}


void Logger::setVerbose (bool verbose)
{
  _verbose = verbose;
}


void Logger::stop ()
{
  if (!_started)
    return;

  switch (_engine)
    {
    case OUT_CONSOLE:
      break;
    case OUT_FILE:
      _fout.close ();
      break;
    case OUT_SYSLOG:
      closelog ();
      break;
    default:
      break;
    }
  _started = false;
}
