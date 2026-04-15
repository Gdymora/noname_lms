cd ~/PHP/noname_lms

# прибираємось (якщо ще не зробили)
rm -f docker-compose.yml
[ -f env.local ] && mv env.local .env

# скрипт + права
chmod +x dev_deploy.sh


```bash
cd ~/PHP/noname_lms

# Переконатись що лежить полегшений Dockerfile
grep "modified for NoName" docker/8.4/Dockerfile

# Запуск з живим логом
DOCKER_BUILDKIT=0 ./dev_deploy.sh 2>&1 | tee build.log
```


Коли закінчить — дивись:

```bash
tail -50 build.log
docker compose ps
```

Якщо все ок — побачите `✅ DEPLOY SUCCESSFUL!` і всі 5 контейнерів в стані `Up`. Якщо впаде — у `build.log` буде точна причина, розберемо.