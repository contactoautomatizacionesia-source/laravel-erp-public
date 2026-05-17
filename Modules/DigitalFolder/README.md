# Digital Folder Module - File Explorer

## Overview

This module provides a complete file explorer system for managing digital folders and files within the application. It includes both backend (admin) and frontend (customer) interfaces.

## Structure

```
Modules/DigitalFolder/
├── Config/
│   └── config.php
├── Database/
│   └── Migrations/
├── Entities/
│   ├── Folder.php          # Folder model
│   └── FolderFile.php      # File model
├── Http/
│   └── Controllers/
│       └── FileExplorerController.php
├── Providers/
│   ├── DigitalFolderServiceProvider.php
│   └── RouteServiceProvider.php
├── Resources/
│   └── views/
│       ├── backend/
│       │   └── file_explorer.blade.php
│       └── frontend/
│           └── file_explorer.blade.php
├── Routes/
│   └── web.php
└── README.md
```

## Database Tables

### folders
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | Folder name |
| slug | varchar(255) | Unique slug |
| parent_id | bigint | Parent folder reference |
| owner_id | bigint | User who owns the folder |
| type | enum | master, user, regular |
| description | text | Optional description |
| is_active | boolean | Active status |

### folder_user (Pivot)
| Column | Type | Description |
|--------|------|-------------|
| folder_id | bigint | Folder reference |
| user_id | bigint | User reference |
| permission | enum | read, write, admin |

### folder_files
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| folder_id | bigint | Folder reference |
| uploaded_by | bigint | User who uploaded |
| name | varchar(255) | Unique generated filename |
| original_name | varchar(255) | Original filename |
| path | varchar(255) | Storage path |
| mime_type | varchar(255) | MIME type |
| extension | varchar(20) | File extension |
| size | bigint | File size in bytes |

## Folder Types

- **master**: Root folder, only one exists. Contains all user folders.
- **user**: Personal folder for each user, created inside master.
- **regular**: Subfolders created by users inside their user folder.

## Permission Levels

| Level | Value | Capabilities |
|-------|-------|--------------|
| read | 1 | View and download files |
| write | 2 | read + create folders/upload files |
| admin | 3 | write + delete and manage permissions |

## Routes

### Admin Routes (prefix: `/admin/file-explorer`)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | / | admin.file-explorer.index | Main explorer view |
| GET | /contents | admin.file-explorer.contents | AJAX folder contents |
| POST | /folder/create | admin.file-explorer.folder.create | Create folder |
| POST | /folder/user-create | admin.file-explorer.folder.user-create | Create user folder |
| POST | /folder/rename | admin.file-explorer.folder.rename | Rename folder |
| POST | /folder/delete | admin.file-explorer.folder.delete | Delete folder |
| POST | /files/upload | admin.file-explorer.files.upload | Upload files |
| GET | /files/download/{id} | admin.file-explorer.download | Download file |
| POST | /files/delete | admin.file-explorer.files.delete | Delete file |
| POST | /files/rename | admin.file-explorer.files.rename | Rename file |
| GET | /files/preview/{id} | admin.file-explorer.files.preview | Preview file |
| GET | /files/serve/{id} | admin.file-explorer.serve | Serve file |
| GET | /search | admin.file-explorer.search | Search files |

### Frontend Routes (prefix: `/profile/file-explorer`)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | / | frontend.file-explorer.index | Customer file explorer |
| GET | /folder/{id} | frontend.file-explorer.folder | Navigate to folder |
| GET | /contents/{id} | frontend.file-explorer.contents | AJAX folder contents |
| GET | /download/{id} | frontend.file-explorer.download | Download file |
| GET | /preview/{id} | frontend.file-explorer.preview | Preview file |
| GET | /serve/{id} | frontend.file-explorer.serve | Serve file |

## Usage Examples

### Creating a User Folder (Superadmin)
```php
use Modules\DigitalFolder\Entities\Folder;
use App\Models\User;

$user = User::find($userId);
$folder = Folder::createUserFolder($user);
```

### Checking Access
```php
$folder = Folder::find($folderId);
$user = auth()->user();

if ($folder->userHasAccess($user, 'write')) {
    // User can write to this folder
}
```

### Getting Breadcrumb
```php
$folder = Folder::find($folderId);
$breadcrumb = $folder->getBreadcrumb();
// Returns Collection of folders from root to current
```

## File Storage

Files are stored in: `storage/app/file_explorer/{Y}/{m}/{filename}`

- Filename is a random 32-character string + original extension
- Original filename is preserved in `original_name` column
- Maximum file size: 50MB

## Access Control Logic

1. **Superadmin**: Full access to everything
2. **Owner** (`owner_id === user.id`): Full access to own folder
3. **Explicit Permission**: Check `folder_user` pivot table
   - Compare user's permission level with required level
   - Hierarchy: admin(3) > write(2) > read(1)

## JavaScript Object

The frontend uses a `FileExplorer` JavaScript object with methods:
- `navigateTo(folderId)` - Navigate to folder
- `refresh()` - Reload current folder
- `showUploadModal()` - Open upload dialog
- `showNewFolderModal()` - Open new folder dialog
- `previewFile(fileId)` - Preview file
- `downloadItem()` - Download selected file
- `deleteItem()` - Delete selected item
- `search(query)` - Filter visible items

## Backward Compatibility

For backward compatibility, alias classes exist in `app/Models/`:
- `App\Models\Folder` → extends `Modules\DigitalFolder\Entities\Folder`
- `App\Models\FolderFile` → extends `Modules\DigitalFolder\Entities\FolderFile`

**Note:** New code should use the module entities directly.

## Troubleshooting

### File not downloading
- Check if file exists in storage: `Storage::exists($file->path)`
- Verify user has read permission on folder

### Cannot create folder
- Ensure parent folder exists
- User needs write permission on parent folder

### Upload failing
- Check file size (max 50MB)
- Verify folder_id is valid
- User needs write permission

## Quick Reference

| Task | Location |
|------|----------|
| Add new folder type | `Folder.php` + migration |
| Change max file size | `FileExplorerController@uploadFiles` |
| Add new file preview type | `FolderFile@isPreviewable()` |
| Modify icon mapping | `FolderFile@getIconClassAttribute()` |
| Change storage path | `FileExplorerController@uploadFiles` |
| Add new permission level | Migration + `Folder@userHasAccess()` |
