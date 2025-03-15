-- 創建管理員角色表
CREATE TABLE IF NOT EXISTS admin_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
);

-- 創建管理員權限表
CREATE TABLE IF NOT EXISTS admin_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
);

-- 創建角色權限關聯表
CREATE TABLE IF NOT EXISTS admin_role_permissions (
    role_id INT,
    permission_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES admin_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE
);

-- 修改現有的admin_users表，添加角色關聯
ALTER TABLE admin_users
ADD COLUMN role_id INT,
ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active',
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at DATETIME,
ADD FOREIGN KEY (role_id) REFERENCES admin_roles(id);

-- 創建管理員操作日誌表
CREATE TABLE IF NOT EXISTS admin_action_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id)
);

-- 插入基本角色數據
INSERT INTO admin_roles (role_name, description) VALUES
('super_admin', '超級管理員 - 擁有所有權限'),
('admin', '一般管理員 - 擁有大部分管理權限'),
('operator', '操作員 - 擁有基本操作權限');

-- 插入基本權限數據
INSERT INTO admin_permissions (permission_name, description) VALUES
('manage_users', '管理使用者權限'),
('manage_roles', '管理角色權限'),
('view_logs', '查看日誌權限'),
('manage_orders', '管理訂單權限'),
('manage_games', '管理遊戲權限');

-- 設置超級管理員角色權限
INSERT INTO admin_role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM admin_roles WHERE role_name = 'super_admin'),
    id
FROM admin_permissions;