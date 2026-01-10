# Docker News Network Assignmet Notes 

## Biggest Vps Problem Faced
The hard part was how uploading the db container since I used Back4App vps and it doesn't support mysql directly.

**Solution:**
I set `DB_HOST` to `db` (the service name in `docker-compose.yml`), on Back4App then llinked it with another hosting specifcly for mysql Databases.