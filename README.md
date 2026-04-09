# coachtech-attendance

## アプリケーション概要
- Laravel 8 を使用したフリーマーケットアプリケーションです。
- Dockerで開発環境を構築可能。
```text
- 会員登録・ログイン機能（メール認証付き）
- 日時・月情報取得機能
- ステータス確認機能
- 出勤機能・休憩機能・退勤機能
- 勤怠一覧情報取得機能
- 詳細遷移機能
- 修正申請機能
- 承認機能
- CSV出力機能
```

---

## 環境構築手順

1. **リポジトリをクローン**
```bash
git clone git@github.com:saito-himeka/coachtech-fleamarket.git
cd coachtech-fleamarket
```

2. **Dockerコンテナを起動**
```bash
docker-compose up -d --build
```

3. **プロジェクト直下で、以下のコマンドを実行する**
```bash
make init
```


## PHPUnitを利用したテスト
```bash
php artisan test
```

## 使用技術/バージョン
- **Backend**: Laravel 8.83.29 (PHP 8.1.33)
- **Frontend**: Blade, CSS, JavaScript
- **Database**: MySQL 8.0.26
- **Infrastructure**: Docker, Nginx 1.21.1
- **External API**: Stripe (決済処理)

## メール認証の設定 (Mailtrap)
ローカルでのメール送信テストには Mailtrap を使用しています。
`.env` ファイルの以下の項目に、ご自身の Mailtrap 認証情報を設定してください。

```text
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=（ユーザー名）
MAIL_PASSWORD=（パスワード）
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@example.com"
```

## テストアカウント
name:管理者
email:admin@example.com
password:password123

name:山田太郎
email:yamada@example.com
password:password123
```text
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

## URL
- 開発環境:http://localhost
- ユーザー登録:http://localhost/register
- phpMyAdmin:http://localhost:8080
    - ユーザー名:laravel_user
    - パスワード:laravel_pass

## ER図

![ER図](./docs/er-diagram.png)

