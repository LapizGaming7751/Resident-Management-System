# QR码Exit-Blocked功能说明

## 功能概述
新增的Exit-Blocked功能允许居民配置QR码的退出行为：
- **正常模式 (is_blocked = 0)**: QR码可以正常进入和退出
- **Exit-Blocked模式 (is_blocked = 1)**: QR码可以进入，但无法退出，直到居民解除阻止

## 数据库更新
1. 在`codes`表中添加了`is_blocked`字段：
   ```sql
   ALTER TABLE `codes` ADD COLUMN `is_blocked` tinyint(1) NOT NULL DEFAULT 0;
   ```

## 功能实现

### 1. 居民界面更新
- **管理页面** (`resident/manage.php`):
  - 显示每个QR码的Exit状态（正常/Exit-Blocked）
  - 添加"Block Exit"/"Unblock Exit"按钮
  - 实时切换QR码的Exit-Blocked状态

- **生成QR码页面** (`resident/generateQR.php`):
  - 添加"Block Exit"复选框选项
  - 创建QR码时可以选择Exit-Blocked状态

- **编辑QR码页面** (`resident/editQR.php`):
  - 添加"Block Exit"复选框
  - 可以修改现有QR码的Exit-Blocked状态

### 2. 安全扫描器更新
- **扫描器** (`security/scanner.php`):
  - 改进消息显示，区分成功和错误消息
  - 使用表情符号和颜色编码显示状态

### 3. API更新
- **扫描逻辑** (`api.php`):
  - 在退出扫描时检查`is_blocked`状态
  - 如果QR码被Exit-Blocked，拒绝退出并显示相应消息
  - 添加切换Exit-Blocked状态的API端点

## 使用方法

### 居民操作
1. **创建Exit-Blocked QR码**:
   - 在生成QR码页面勾选"Block Exit"选项
   - 访客可以进入但无法退出

2. **管理现有QR码**:
   - 在管理页面点击"Block Exit"或"Unblock Exit"按钮
   - 或在编辑页面修改Exit-Blocked状态

### 安全人员操作
1. **扫描Exit-Blocked QR码**:
   - 首次扫描：正常进入
   - 第二次扫描：显示"Exit denied"消息
   - 需要联系居民解除阻止

## 技术细节

### API端点
- `PUT /api.php` with `type: "toggle_exit_block"`: 切换Exit-Blocked状态
- `POST /api.php` with `type: "guest"`: 扫描QR码（包含Exit-Blocked检查）

### 数据库字段
- `is_blocked`: tinyint(1), 默认值0
  - 0: 正常模式
  - 1: Exit-Blocked模式

### 错误处理
- Exit-Blocked QR码尝试退出时返回错误消息
- 包含访客信息、居民信息和房间号
- 提供清晰的用户反馈

## 部署说明
1. 运行`update_database.sql`更新数据库
2. 确保所有文件已更新
3. 测试功能是否正常工作

## 注意事项
- Exit-Blocked功能不影响QR码的进入权限
- 只有居民可以解除Exit-Blocked状态
- 安全人员无法绕过Exit-Blocked限制
