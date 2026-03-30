# Arduino Motion Management (Symfony)
Système Symfony 8 pour gérer des appareils Arduino physiques avec détection de mouvement.

## Features
- **Enregistrement des appareils**: `POST /auth`
- **Enregistrement des mouvements**: `POST /motion` (appareil validé uniquement)
- **Monitoring**: Vérification de connectivité toutes les 20 secondes
- **Dashboard**: Interface web pour valider et suivre les appareils
- **Tests**: PHPUnit via `--env=test`

## Stack
- **Symfony 8.0** + PHP 8.4
- **PostgreSQL 16** + Doctrine ORM
- **Docker Compose** (web, worker, database, adminer)
- **PHPUnit 13**

## Démarrage rapide
```bash
make build      # Build les images Docker
make up         # Lance les services
make test       # Lance les tests unitaires
make down       # Arrête les services
```

## Services
- **Dashboard**: http://localhost:8000
- **Adminer**: http://localhost:8080 (user: arduino / pass: arduino)

## API
- `POST /auth` - Enregistrer/toucher un appareil
- `POST /motion` - Enregistrer un mouvement (appareils validés uniquement)
- `GET /api/devices` - Lister tous les appareils
- `POST /api/devices/{id}/validate` - Valider et nommer un appareil
- `GET /api/motions` - Lister les mouvements récents

## Arduino physique
Fais pointer ton Arduino vers `http://<IP_DU_SERVEUR>:8000` puis envoie:
- `POST /auth` au démarrage
- `POST /motion` à chaque détection

## Tests
```bash
make test       # PHPUnit avec --env=test (DB suffixée _test)
```
Les tests utilisent la même configuration DB avec le suffixe `_test`.
