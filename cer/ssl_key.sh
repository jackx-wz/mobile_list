cur_dir=`cd $(dirname $0);pwd`
blackhole="/dev/null"
ip=`ifconfig | grep 'inet' | grep -v '127.0.0.1' | grep -v 'inet6' | cut -d't' -f2 | cut -d':' -f2 | awk '{print $1}'`

if [ "" != "$1" ]; then
    ip=$1
fi

openssl genrsa -out "$cur_dir"/myCA.key 2048 2> $blackhole

openssl req -x509 -new -key "$cur_dir"/myCA.key -out "$cur_dir"/myCA.cer -days 730 -subj /CN="App download tolls CA" 2> $blackhole

openssl genrsa -out "$cur_dir"/server.key 2048 2> $blackhole

openssl req -new -out "$cur_dir"/server.req -key "$cur_dir"/server.key -subj /CN=$ip 2> $blackhole

openssl x509 -req -in "$cur_dir"/server.req -out "$cur_dir"/server.cer -CAkey "$cur_dir"/myCA.key -CA "$cur_dir"/myCA.cer -days 365 -CAcreateserial -CAserial "$cur_dir"/serial 2> $blackhole

if [ ! -d "$cur_dir/../public/apps" ]; then
    mkdir $cur_dir/../public/apps
fi

chmod a+w $cur_dir/../public/apps
