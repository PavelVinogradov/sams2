using namespace std;

#include "config.h"

#include <stdio.h>
#include <stdlib.h>

# ifndef __STDC_LIMIT_MACROS
# define __STDC_LIMIT_MACROS 1 /* to make it work also in C++ mode */
# endif
# ifndef __STDC_CONSTANT_MACROS
# define __STDC_CONSTANT_MACROS 1 /* to make it work also in C++ mode */
# endif
#include <stdint.h>

#include <string.h>
#include <limits.h>

#if HAVE_SYS_MOUNT_H
#include <sys/mount.h>
#endif

#if HAVE_MNTENT_H
#include <mntent.h>
#endif

#if HAVE_SYS_STATVFS_H
#include <sys/statvfs.h>
#endif

#include "human.h"

struct fs_info
{
  char *fs_dev;                 /* FS device */
  char *fs_type;                /* FS type */
  char *fs_disk;                /* FS mount point */
  uintmax_t fsu_blocksize;      /* Size of a block.  */
  uintmax_t fsu_blocks;         /* Total blocks. */
  uintmax_t fsu_bfree;          /* Free blocks available to superuser. */
  uintmax_t fsu_bavail;         /* Free blocks available to non-superuser. */
  bool fsu_bavail_top_bit_set;  /* 1 if fsu_bavail represents a value < 0.  */
  uintmax_t fsu_files;          /* Total file nodes. */
  uintmax_t fsu_ffree;          /* Free file nodes. */

  struct fs_info * next;
};

#ifndef ME_DUMMY
# define ME_DUMMY(Fs_name, Fs_type)             \
    (strcmp (Fs_type, "autofs") == 0            \
     || strcmp (Fs_type, "none") == 0           \
     || strcmp (Fs_name, "none") == 0           \
     || strcmp (Fs_type, "proc") == 0           \
     || strcmp (Fs_type, "subfs") == 0          \
     || strcmp (Fs_type, "sysfs") == 0          \
     || strcmp (Fs_type, "devpts") == 0         \
     || strcmp (Fs_type, "rpc_pipefs") == 0     \
     /* for NetBSD 3.0 */                       \
     || strcmp (Fs_type, "kernfs") == 0         \
     /* for Irix 6.5 */                         \
     || strcmp (Fs_type, "ignore") == 0)
#endif

#ifndef ME_REMOTE
/* A file system is `remote' if its Fs_name contains a `:'
   or if (it is of type (smbfs or cifs) and its Fs_name starts with `//').  */
# define ME_REMOTE(Fs_name, Fs_type)            \
    (strchr (Fs_name, ':') != NULL              \
     || ((Fs_name)[0] == '/'                    \
         && (Fs_name)[1] == '/'                 \
         && (strcmp (Fs_type, "smbfs") == 0     \
             || strcmp (Fs_type, "cifs") == 0)))
#endif

/* Many space usage primitives use all 1 bits to denote a value that is
   not applicable or unknown.  Propagate this information by returning
   a uintmax_t value that is all 1 bits if X is all 1 bits, even if X
   is unsigned and narrower than uintmax_t.  */
#define PROPAGATE_ALL_ONES(x) \
  ((sizeof (x) < sizeof (uintmax_t) \
    && (~ (x) == (sizeof (x) < sizeof (int) \
                  ? - (1 << (sizeof (x) * CHAR_BIT)) \
                  : 0))) \
   ? UINTMAX_MAX : (uintmax_t) (x))

/* Extract the top bit of X as an uintmax_t value.  */
#define EXTRACT_TOP_BIT(x) ((x) \
                            & ((uintmax_t) 1 << (sizeof (x) * CHAR_BIT - 1)))

/* If a value is negative, many space usage primitives store it into an
   integer variable by assignment, even if the variable's type is unsigned.
   So, if a space usage variable X's top bit is set, convert X to the
   uintmax_t value V such that (- (uintmax_t) V) is the negative of
   the original value.  If X's top bit is clear, just yield X.
   Use PROPAGATE_TOP_BIT if the original value might be negative;
   otherwise, use PROPAGATE_ALL_ONES.  */
#define PROPAGATE_TOP_BIT(x) ((x) | ~ (EXTRACT_TOP_BIT (x) - 1))


void free_fs_info(struct fs_info **ptr)
{
  if (!ptr || !*ptr)
    return;

  if ((*ptr)->fs_dev)
    free((*ptr)->fs_dev);
  if ((*ptr)->fs_type)
    free((*ptr)->fs_type);
  if ((*ptr)->fs_disk)
    free((*ptr)->fs_disk);

  free(*ptr);
}

int get_fs_usage (struct fs_info *fsp)
{
  struct statvfs fsd;

  //printf ("\n========disk: %s==========\n", fsp->fs_disk);
  if (statvfs (fsp->fs_disk, &fsd) < 0)
    return -1;

  /* f_frsize isn't guaranteed to be supported.  */
  fsp->fsu_blocksize = (fsd.f_frsize
                        ? PROPAGATE_ALL_ONES (fsd.f_frsize)
                        : PROPAGATE_ALL_ONES (fsd.f_bsize));

  fsp->fsu_blocks = PROPAGATE_ALL_ONES (fsd.f_blocks);
  fsp->fsu_bfree = PROPAGATE_ALL_ONES (fsd.f_bfree);
  fsp->fsu_bavail = PROPAGATE_TOP_BIT (fsd.f_bavail);
  fsp->fsu_bavail_top_bit_set = EXTRACT_TOP_BIT (fsd.f_bavail) != 0;
  fsp->fsu_files = PROPAGATE_ALL_ONES (fsd.f_files);
  fsp->fsu_ffree = PROPAGATE_ALL_ONES (fsd.f_ffree);

  return 0;
}

char * print_header (void)
{
  char *str_header;
  char str_res[1024];

  str_res[0] = '\0';
  strcpy (str_res, "  <th>Filesystem</th>\n");
  strcat (str_res, "  <th>Size</th>\n");
  strcat (str_res, "  <th>Used</th>\n");
  strcat (str_res, "  <th>Avail</th>\n");
  strcat (str_res, "  <th>Use%</th>\n");
  strcat (str_res, "  <th>Mounted on</th>\n");

  str_header = (char*) malloc(strlen(str_res)+1);
  strcpy (str_header, str_res);
  return str_header;
}

char * print_dev (struct fs_info * fsu)
{
  uintmax_t input_units;
  uintmax_t output_units;
  uintmax_t total;
  uintmax_t available;
  bool negate_available;
  uintmax_t available_to_root;
  uintmax_t used;
  bool negate_used;
  double pct = -1;
  char *str_dev;
  char str_res[1024];
  char str_tmp[256];
  char buf[3][LONGEST_HUMAN_READABLE + 2];

  uintmax_t output_block_size = 1; // The units to use when printing sizes

  input_units = fsu->fsu_blocksize;
  output_units = output_block_size;
  total = fsu->fsu_blocks;
  available = fsu->fsu_bavail;
  negate_available = (fsu->fsu_bavail_top_bit_set
                      & (available != UINTMAX_MAX));
  available_to_root = fsu->fsu_bfree;

  used = UINTMAX_MAX;
  negate_used = false;
  if (total != UINTMAX_MAX && available_to_root != UINTMAX_MAX)
    {
      used = total - available_to_root;
      negate_used = (total < available_to_root);
    }

  str_res[0] = '\0';
  strcat (str_res, "<tr>");
  sprintf (str_tmp, "  <td>%s</td>\n", fsu->fs_dev);
  strcat (str_res, str_tmp);

  sprintf (str_tmp, "  <td align=\"center\">%s</td>\n", df_readable (false, total, buf[0], input_units, output_units));
  strcat (str_res, str_tmp);

  sprintf (str_tmp, "  <td align=\"center\">%s</td>\n", df_readable (negate_used, used, buf[1], input_units, output_units));
  strcat (str_res, str_tmp);

  sprintf (str_tmp, "  <td align=\"center\">%s</td>\n", df_readable (negate_available, available, buf[2], input_units, output_units));
  strcat (str_res, str_tmp);



  if (used == UINTMAX_MAX || available == UINTMAX_MAX)
    ;
  else if (!negate_used
           && used <= TYPE_MAXIMUM (uintmax_t) / 100
           && used + available != 0
           && (used + available < used) == negate_available)
    {
      uintmax_t u100 = used * 100;
      uintmax_t nonroot_total = used + available;
      pct = u100 / nonroot_total + (u100 % nonroot_total != 0);
    }
  else
    {
      /* The calculation cannot be done easily with integer
         arithmetic.  Fall back on floating point.  This can suffer
         from minor rounding errors, but doing it exactly requires
         multiple precision arithmetic, and it's not worth the
         aggravation.  */
      double u = negate_used ? - (double) - used : used;
      double a = negate_available ? - (double) - available : available;
      double nonroot_total = u + a;
      if (nonroot_total)
        {
          pct = (u * 100 / nonroot_total);
          long int lipct = (long int) pct;
          double ipct = lipct;

          /* Like `pct = ceil (dpct);', but avoid ceil so that
             the math library needn't be linked.  */
          if (ipct - 1 < pct && pct <= ipct + 1)
            pct = ipct + (ipct < pct);
        }
    }

  if (0 <= pct)
    sprintf (str_tmp, "  <td align=\"center\">%.0f%%</td>\n", pct);
  else
    sprintf (str_tmp, "  <td align=\"center\">%s</td>\n", "- ");
  strcat (str_res, str_tmp);


  sprintf (str_tmp, "  <td>%s</td>\n", fsu->fs_disk);
  strcat (str_res, str_tmp);

  strcat (str_res, "</tr>");

  str_dev = (char*) malloc(strlen(str_res)+1);
  strcpy (str_dev, str_res);
  return str_dev;
}

char * get_fsusage()
{
  struct fs_info *fs_list = NULL;
  struct fs_info *fs_prev = NULL;
  struct fs_info *fs_cur = NULL;
  bool file_systems_processed = false;
  char *header = NULL;
  char *res = NULL;
  char *dev = NULL;
  char *footer = NULL;



#ifdef HAVE_GETMNTENT
  {
    struct mntent *mnt = NULL;
    FILE *fp;

    fp = setmntent (MOUNTED, "r");
    if (fp == NULL)
      return NULL;

    while ((mnt = getmntent (fp)))
      {
        fs_cur = (struct fs_info *)malloc(sizeof(struct fs_info));
        if (!fs_list)
          fs_list = fs_cur;

        fs_cur->fs_dev = strdup(mnt->mnt_fsname);
        fs_cur->fs_disk = strdup(mnt->mnt_dir);
        fs_cur->fs_type = strdup(mnt->mnt_type);

        fs_cur->next = NULL;
        if (fs_prev)
          fs_prev->next = fs_cur;
        fs_prev = fs_cur;
      }

    endmntent (fp);
  }
#endif // HAVE_GETMNTENT



#ifdef HAVE_GETMNTINFO
  {
    struct statfs *fsp;
    int entries;

    entries = getmntinfo (&fsp, MNT_NOWAIT);
    if (entries < 0)
      return NULL;

    for (; entries-- > 0; fsp++)
      {
        fs_cur = (struct fs_info *)malloc(sizeof(struct fs_info));
        if (!fs_list)
          fs_list = fs_cur;

        fs_cur->fs_dev = strdup(fsp->f_mntfromname);
        fs_cur->fs_disk = strdup(fsp->f_mntonname);
        fs_cur->fs_type = strdup(fsp->f_fstypename);

        fs_cur->next = NULL;
        if (fs_prev)
          fs_prev->next = fs_cur;
        fs_prev = fs_cur;
      }
  }
#endif // HAVE_GETMNTINFO


  if (!fs_list)
    return NULL;

  while (fs_list)
    {
      fs_cur = fs_list;
      fs_list = fs_list->next;

      if (ME_DUMMY(fs_cur->fs_dev, fs_cur->fs_type) || ME_REMOTE(fs_cur->fs_dev, fs_cur->fs_type))
        {
          free_fs_info(&fs_cur);
          continue;
        }
      if (get_fs_usage (fs_cur))
        {
          free_fs_info(&fs_cur);
          continue;
        }
      if (fs_cur->fsu_blocks == 0)
        {
          free_fs_info(&fs_cur);
          continue;
        }

      if (! file_systems_processed)
        {
          res = strdup ("<table width=100% class=sysplugtable>\n");

          file_systems_processed = true;
          header = print_header ();
          res = (char *) realloc (res, strlen (res) + strlen (header)+1);
          strcat (res, header);
          free (header);
        }

      dev = print_dev (fs_cur);

      res = (char *) realloc (res, strlen (res) + strlen (dev)+1);
      strcat (res, dev);
      free (dev);
      free_fs_info(&fs_cur);
    }

  footer = strdup ("</table>\n");
  res = (char *) realloc (res, strlen (res) + strlen (footer)+1);
  strcat (res, footer);
  free (footer);

  return res;
}
