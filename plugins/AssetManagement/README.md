# Assets & Resources Plugin for Taskify SaaS

A comprehensive SaaS-ready plugin for managing organizational assets with features for tracking, assignment, lending, and maintaining complete asset lifecycle history within Taskify SaaS.

## Features

### 📦 Asset Management
- **Asset Registration**: Add assets with detailed information (name, asset tag, description, category, status, purchase details)
- **Asset Tracking**: Track asset status (available, assigned, lent, non-functional, lost, damaged, under-maintenance)
- **Media Support**: Upload and manage asset images with integrated media library
- **Unique Asset Tags**: Each asset has a unique identifier for easy tracking and auditing
- **Custom Fields**: Add purchase costs, and custom notes for each asset
- **Asset Duplication**: Clone existing assets with new unique tags for similar items

### 🏷️ Category Management
- **Asset Categories**: Organize assets by categories (Laptops, Monitors, Furniture, Mobile Devices, Access Cards, etc.)
- **Category Operations**: Create, update, delete, and bulk manage categories
- **Color-Coded Organization**: Visual identification with customizable category colors
- **Category-Based Filtering**: Quick filtering and searching by asset categories

### 👥 Asset Assignment & Lending System
- **Assign to Users**: Assign assets to team members with return date tracking
- **Lending Management**: Lend assets temporarily with estimated return dates
- **Return Processing**: Process asset returns with notes and automatic status updates
- **Bulk Assignment**: Assign multiple assets to users simultaneously (ideal for onboarding)
- **Current Holder Tracking**: Real-time tracking of who currently has each asset
- **Assignment History**: Complete audit trail of all assignments and transfers

### 📊 Analytics & Reporting
- **Global Analytics Dashboard**: Visual overview of asset status distribution and user assignments
- **Asset Lifecycle History**: Complete audit trail of all asset operations and status changes
- **Export Functionality**: Export asset data to Excel/CSV for reporting and backup
- **Import Support**: Bulk import assets from Excel/CSV files for rapid deployment
- **Purchase Reports**: Track asset purchases, costs, and depreciation

### 🔍 Advanced Features
- **Search & Filter**: Advanced search by name, tag, description with category and status filters
- **Multi-Tenant Support**: Workspace-aware asset management tied to Taskify company profiles
- **Permission-Based Access**: Role-based access control integrated with Taskify's Spatie permission system
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Real-Time Updates**: Instant status updates

### 🔐 Security & Compliance
- **Role Integration**: Leverages Taskify's existing role system for secure access control
- **Audit Logs**: Complete history of who did what and when
- **Workspace Isolation**: Assets are isolated per workspace/company
- **Data Privacy**: Tenant data separation and privacy compliance

## Installation

### Requirements
- Taskify SaaS v2.0.0 or higher
- Super Admin access to Taskify SaaS for installation
- PHP 8.0+
- MySQL 5.7+ or compatible database
- Modern web browser (Chrome, Firefox, Safari, Edge)

### Installation Steps

1. **Download Plugin**
   - Obtain the plugin ZIP file from CodeCanyon or Infinitietech

2. **Install via Admin Panel**
   - Login as Super Admin
   - Navigate to Settings → Plugins
   - Click "Upload Plugin"
   - Select the downloaded ZIP file
   - Click "Install"

3. **Clear Cache**
   - Clear cache: Settings → System → Clear Cache

4. **Entitle Admins**
   - Assign the Assets plan to desired workspaces/subscriptions
   - Entitled admins will see "Assets" in their sidebar

5. **Verify Installation**
   - Login as an entitled admin
   - Check for "Assets" menu item in sidebar
   - Access Assets section to begin setup

## Usage

### Getting Started

1. **Create Categories**
   - Navigate to Assets → Categories
   - Add categories: Laptops, Monitors, Furniture, Mobile, etc.
   - Assign colors for visual identification

2. **Add Assets**
   - Click "Add New Asset"
   - Fill in details: Name, Asset Tag, Category, Purchase Date, etc.
   - Upload asset images if available
   - Save asset

3. **Assign Assets**
   - Select asset from list
   - Click "Assign" or "Lend"
   - Choose user and set return date (if applicable)
   - Add notes if needed

4. **Track & Manage**
   - View asset history and status
   - Process returns when assets come back
   - Update asset status as needed
   - Export reports for audits

### Bulk Operations

**Bulk Import**
- Prepare CSV/Excel file with columns: asset_name, category, asset_tag, purchase_date, status, assigned_to, notes
- Navigate to Assets → Import
- Upload file


**Bulk Assignment**
- Select multiple assets from list
- Click "Bulk Assign"
- Choose user and add notes if needed
- Confirm assignment

## Models

### Asset
- **Attributes**:
  - name, asset_tag, description, category_id, status
  - purchase_date, purchase_cost
  - assigned_to, admin_id
- **Relationships**:
  - belongsTo User (assignedUser, creator)
  - belongsTo AssetCategory
  - belongsTo Workspace
  - hasMany AssetHistory
- **Scopes**: Available assets, workspace scoped

### AssetCategory
- **Attributes**: name, description, color, admin_id
- **Relationships**:
  - hasMany Asset
  - belongsTo Workspace(admin)
- **Features**: Color-coded badges, workspace isolation

### AssetHistory
- **Attributes**:
  - asset_id, user_id, action, status_change
  - lent_to, assigned_to, date_given
  - estimated_return_date, actual_return_date
  - returned_by, notes, workspace_id
- **Relationships**:
  - belongsTo Asset, User
  - belongsTo lentToUser, returnedByUser
- **Features**: Complete audit trail, timestamp tracking

## Routes

### Asset Routes
- `GET /master-panel/assets/index` - List all assets (main page with filters)
- `GET /master-panel/assets/show/{id}` - View asset details
- `POST /master-panel/assets/store` - Create a new asset
- `POST /master-panel/assets/update/{id}` - Update an existing asset
- `DELETE /master-panel/assets/destroy/{id}` - Delete an asset
- `DELETE /master-panel/assets/destroy_multiple` - Bulk delete assets
- `POST /master-panel/assets/{id}/lend` - Lend asset temporarily
- `POST /master-panel/assets/{id}/return` - Return asset
- `POST /master-panel/assets/duplicate/{id}` - Clone asset
- `POST /master-panel/assets/bulk-assign` - Bulk assign assets
- `GET /master-panel/assets/global-analytics` - Analytics dashboard for all assets
- `GET /master-panel/assets/search-assets` - Search assets dynamically
- `POST /master-panel/assets/import` - Import assets from Excel/CSV
- `GET /master-panel/assets/export` - Export assets to Excel/CSV

### Category Routes
- `GET /master-panel/assets/category/index` - List categories
- `GET /master-panel/assets/category/list` - Fetch paginated list of categories
- `POST /master-panel/assets/category/store` - Create category
- `POST /master-panel/assets/category/update/{id}` - Update category
- `DELETE /master-panel/assets/category/destroy/{id}` - Delete category
- `DELETE /master-panel/assets/category/destroy_multiple` - Bulk delete categories

## Permissions

### Super Admin
- Install and activate plugin
- Manage plugin settings
- Entitle workspaces/subscriptions

### Workspace Admin
- Full CRUD operations on assets and categories within workspace
- Assign/lend assets to team members
- View analytics and reports
- Import/export asset data
- Manage asset categories

### Regular Users
- View assets assigned to them
- Return assets
- Access limited to workspace scope

## Status Types

- **Available**: Asset is ready for assignment
- **Lent**: Asset is lent to a user
- **Non-functional**: Asset is not working properly
- **Lost**: Asset cannot be located
- **Damaged**: Asset is damaged but potentially repairable
- **Under-maintenance**: Asset is being serviced or repaired

## CSV/Excel Import Template

```csv
asset_name,category,asset_tag,purchase_date,warranty_end,status,assigned_to,notes
MacBook Air M2,Laptops,SAAS-LT-1001,2025-02-10,2026-02-10,assigned,jane@example.com,Company issued
Dell Monitor 27",Monitors,SAAS-MN-015,2025-01-15,2026-01-15,available,,IT Department
iPhone 15 Pro,Mobile Devices,SAAS-MB-032,2024-12-20,2025-12-20,lent,john@example.com,Temporary assignment
```

### Import Field Descriptions
- **asset_name**: Name/model of the asset (required)
- **category**: Category name (must exist or will be created)
- **asset_tag**: Unique identifier (required, must be unique)
- **purchase_date**: Date asset was purchased (YYYY-MM-DD)
- **status**: Current status (available, assigned, lent, etc.)
- **assigned_to**: User email address (if assigned/lent)
- **notes**: Additional information or remarks

## Database Tables

- `asset_categories` - Asset category definitions (workspace scoped)
- `assets` - Main asset records (workspace scoped)
- `asset_histories` - Asset operation history and audit trail (workspace scoped)

## Best Practices

### Asset Tagging
- Use consistent naming conventions (e.g., DEPT-TYPE-NUMBER)
- Include location codes if managing multiple offices
- Keep tags short but meaningful

### Category Organization
- Create logical, non-overlapping categories
- Use descriptive names
- Assign distinct colors for quick visual identification

### Regular Audits
- Export asset data monthly for backup
- Review asset status regularly
- Clean up inactive/retired assets

### User Training
- Train admins on bulk operations
- Educate users on return process
- Document your asset management workflow
- Create internal guidelines for asset requests

## Troubleshooting

### Plugin Not Visible
- Ensure plugin is activated
- Clear cache: Settings → System → Clear Cache
- Verify workspace is entitled to Assets plan
- Check user permissions

### Import Errors
- Verify CSV format matches template
- Check for duplicate asset tags
- Ensure user emails exist in system
- Validate date formats (YYYY-MM-DD)

### Permission Issues
- Verify user role has necessary permissions
- Check workspace access
- Ensure admin rights for management functions

## Support

### Documentation
- Full documentation available at plugin homepage
- Sample CSV templates

### Contact Support
- **CodeCanyon**: [Infinitietech Profile](https://codecanyon.net/user/infinitietech)
- **Teams Support**: [Join Support Channel](https://teams.live.com/l/invite/FEADpduIPYZUtss7w8)
- **Website**: [infinitietech.com](https://infinitietech.com/)

### Additional Resources
- Changelog and version history
- Feature request portal
- Community forum
- Migration guides

## Version History

See `plugin.json` for current version information.

## License

This plugin is licensed for use with valid Taskify SaaS installations. See license agreement for details.

## Credits

Developed by [Infinitietech](https://infinitietech.com/)

---

*Assets & Resources Plugin provides a complete SaaS-ready asset management solution with tracking, assignment, lending, and comprehensive reporting capabilities for Taskify SaaS.*
