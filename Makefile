init:
	docker-compose up -d --build
	docker-compose exec php composer install
	docker-compose exec php cp .env.example .env
	# 画像フォルダ作成（エラー防止のため -p を付与）
	mkdir -p ./src/storage/app/public/img
	# 画像があれば移動（なければスキップするように頭に - を付与）
	-mv ./src/public/img/copy_storage_img/*.jpg ./src/storage/app/public/img
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan storage:link
	# 権限変更（以前 sudo エラーで苦労された部分の自動化ですね！）
	docker-compose exec php chmod -R 777 storage bootstrap/cache
	@make fresh

fresh:
	docker-compose exec php php artisan migrate:fresh --seed

restart:
	@make down
	@make up

up:
	docker-compose up -d

down:
	docker-compose down --remove-orphans

cache:
	docker-compose exec php php artisan cache:clear 
	docker-compose exec php php artisan config:cache 

stop:
	docker-compose stop

test:
	docker-compose exec php php artisan test