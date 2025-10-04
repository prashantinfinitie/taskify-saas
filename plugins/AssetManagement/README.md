# Asset Management Plugin

A comprehensive Laravel plugin for managing organizational assets with features for tracking, lending, and maintaining asset history.

## Features

### 📦 Asset Management
- **Asset Registration**: Add assets with detailed information (name, tag, description, category, status, purchase details)
- **Asset Tracking**: Track asset status (available, lent, non-functional, lost, damaged, under-maintenance)
- **Media Support**: Upload and manage asset images with Spatie Media Library integration
- **Unique Asset Tags**: Each asset has a unique identifier for easy tracking

### 🏷️ Category Management
- **Asset Categories**: Organize assets by categories with color-coded badges
- **Category Operations**: Create, update, delete, and bulk manage categories

### 👥 Asset Lending System
- **Lend Assets**: Assign assets to users with estimated return dates
- **Return Tracking**: Process asset returns with notes and history
- **Bulk Assignment**: Assign multiple assets to users simultaneously
- **Current Holder Tracking**: Track who currently has each asset

### 📊 Analytics & Reporting
- **Global Analytics**: Dashboard with asset status distribution and user assignments
- **Asset History**: Complete audit trail of all asset operations
- **Export Functionality**: Export asset data to Excel/CSV
- **Import Support**: Bulk import assets from Excel/CSV files

### 🔍 Advanced Features
- **Search & Filter**: Search assets by name, tag, description with category and status filters
- **Duplicate Assets**: Clone existing assets with new unique tags
- **Permission-Based Access**: Role-based access control for different operations
- **Media Storage**: Support for local and S3 storage backends

## Installation

1. Place the plugin in your `Plugins` directory
2. The plugin will auto-register and publish assets
3. Run migrations (handled automatically)

## Models

### Asset
- **Attributes**: name, asset_tag, description, assigned_to, category_id, status, purchase_date, purchase_cost
- **Relationships**: belongsTo User (assignedUser), belongsTo AssetCategory, hasMany AssetHistory
- **Scopes**: Available assets
- **Media**: Supports image uploads

### AssetCategory
- **Attributes**: name, description, color
- **Relationships**: hasMany Asset

### AssetHistory
- **Attributes**: asset_id, user_id, action, lent_to, date_given, estimated_return_date, actual_return_date, returned_by, notes
- **Relationships**: belongsTo Asset, User, lentToUser, returnedByUser

## Routes

### Asset Routes
- `GET /assets/index` - List all assets
- `GET /assets/show/{id}` - View asset details
- `POST /assets/store` - Create new asset
- `POST /assets/update/{id}` - Update asset
- `DELETE /assets/destroy/{id}` - Delete asset
- `POST /assets/{id}/lend` - Lend asset to user
- `POST /assets/{id}/return` - Return asset
- `POST /assets/bulk-assign` - Bulk assign assets
- `GET /assets/global-analytics` - Analytics dashboard
- `POST /assets/import` - Import assets
- `GET /assets/export` - Export assets

### Category Routes
- `GET /assets/category/index` - List categories
- `POST /assets/category/store` - Create category
- `POST /assets/category/update/{id}` - Update category
- `DELETE /assets/category/destroy/{id}` - Delete category

## Permissions

- **Admin/All Data Access**: Full CRUD operations on all assets and categories
- **Regular Users**: Can only view and return assets assigned to them

## Status Types

- **Available**: Asset is ready for use
- **Lent**: Asset is currently assigned to a user
- **Non-functional**: Asset is not working
- **Lost**: Asset cannot be located
- **Damaged**: Asset is damaged but potentially repairable
- **Under-maintenance**: Asset is being serviced

## Requirements

- Laravel Framework
- Spatie Media Library
- Maatwebsite Excel (for import/export)
- Bootstrap (for UI components)

## Database Tables

- `asset_categories` - Asset category definitions
- `assets` - Main asset records
- `asset_histories` - Asset operation history and audit trail

## Version

Current version information is stored in `plugin.json`

---

*This plugin provides a complete asset management solution with tracking, lending, and comprehensive reporting capabilities.*
