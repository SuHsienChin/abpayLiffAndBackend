-- 添加 api_url 欄位到 system_logs 表
ALTER TABLE system_logs ADD COLUMN api_url TEXT AFTER JSON;