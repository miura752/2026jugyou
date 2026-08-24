# 前期課題操作手順書

## Vim のインストールおよび設定
```
sudo yum install vim -y
```
vim ~/.vimrc
```
set expandtab
set tabstop=2
set shiftwidth=2
set autoindent
```

## screen のインストール
```
sudo yum install screen -y
```
## vim ~/.screenrc
```
defutf8 on
defencoding utf8
encoding utf8 utf8
hardstatus alwayslastline "%{= bw}%-w%{= wk}%n%t*%{-}%+w"
```

## Docker のインストールと自動起動化
```
sudo yum install -y docker
sudo systemctl start docker
sudo systemctl enable docker
```
```
sudo usermod -a -G docker ec2-user
```
## Docker Compose のインストール
```
DOCKER_CONFIG=${DOCKER_CONFIG:-$HOME/.docker}
mkdir -p $DOCKER_CONFIG/cli-plugins
curl -SL [https://github.com/docker/compose/releases/download/v5.1.2/docker-compose-linux-x86_64](https://github.com/docker/compose/releases/download/v5.1.2/docker-compose-linux-x86_64) -o $DOCKER_CONFIG/cli-plugins/docker-compose
chmod +x $DOCKER_CONFIG/cli-plugins/docker-compose
```

## buildx のインストール
```
mkdir -p ~/.docker/cli-plugins
ARCH=$(uname -m | sed 's/x86_64/amd64/;s/aarch64/arm64/')
BUILDX_URL=$(curl -s [https://api.github.com/repos/docker/buildx/releases/latest]
(https://api.github.com/repos/docker/buildx/releases/latest) | grep "browser_download_url.*linux-$ARCH" | cut -d '"' -f 4)
curl -L $BUILDX_URL -o ~/.docker/cli-plugins/docker-buildx
chmod +x ~/.docker/cli-plugins/docker-buildx
```

## docker compose 
```
mkdir dockertest
cd dockertest
vim compose.yml
vim Dockerfile
docker compose up
```

## nginx
```
mkdir nginx
mkdir nginx/conf.d
vim nginx/conf.d/default.conf
```

## 作業ディレクトリと設定用ディレクトリの作成
```
mkdir public
vim public/kadai.php
```


## Docker コンテナの起動
```
docker compose up -d
```


## データベースの初期テーブル作成
```
docker compose exec mysql mysql example_db
CREATE TABLE `bbs_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `body` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
```
ALTER TABLE `bbs_entries` ADD COLUMN image_filename TEXT DEFAULT NULL;
```


## 動作確認

http://3.85.93.19/kadai.php
