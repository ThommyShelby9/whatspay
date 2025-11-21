# 🔧 Solution Définitive - Problème de Callback PayPlus

## ❌ Le problème

Lorsque vous faites un dépôt :
1. ✅ L'argent est prélevé de votre compte mobile money
2. ✅ PayPlus enregistre la transaction comme réussie
3. ❌ **Votre wallet n'est jamais crédité** car le callback n'est pas reçu/traité

## ✅ La solution (3 parties)

### Partie 1 : Routes callback améliorées ✓

**Modification apportée :** Les routes callback acceptent maintenant GET et POST

```php
// Avant : Route::post('callback/{transaction}', ...)
// Maintenant : Route::match(['get', 'post'], 'callback/{transaction}', ...)
```

**Pourquoi ?** Certaines passerelles de paiement envoient des callbacks en GET au lieu de POST.

### Partie 2 : Système de vérification automatique ✓

**Nouvelle commande créée :** `php artisan payments:check-pending`

Cette commande :
- ✓ Vérifie toutes les transactions en attente
- ✓ Interroge PayPlus pour connaître leur statut réel
- ✓ Crédite automatiquement le wallet si la transaction est complétée
- ✓ Marque comme échouée si PayPlus dit "failed"

**Programmation automatique :** Toutes les 5 minutes via le scheduler Laravel

### Partie 3 : Endpoint de test ✓

**Nouvelle route créée :** `GET /payment/callback/test`

Permet de tester si PayPlus peut joindre votre serveur depuis Internet.

---

## 🚀 Déploiement (IMPORTANT !)

### Étape 1 : Mettre à jour le code

```bash
# Si vous utilisez Git
git add .
git commit -m "Fix: Système de vérification automatique des callbacks PayPlus"
git push

# Déployer sur le serveur de production
```

### Étape 2 : Activer le scheduler Laravel

**⚠️ CRITIQUE :** Le scheduler Laravel doit tourner pour que la vérification automatique fonctionne !

#### Option A : Via Cron (Recommandé)

Ajoutez cette ligne à votre crontab :

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne (remplacez le chemin par le vôtre)
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

#### Option B : Via Supervisor (pour Docker)

Créez un fichier `supervisor-scheduler.conf` :

```ini
[program:laravel-scheduler]
process_name=%(program_name)s
command=php /var/www/html/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/scheduler.log
```

#### Option C : Vérification manuelle (temporaire)

Pour tester sans cron, exécutez manuellement :

```bash
php artisan schedule:run
```

### Étape 3 : Vérifier que ça fonctionne

#### Test 1 : Endpoint de test accessible

```bash
# Depuis votre navigateur ou terminal
curl https://app-dev.whatspay.africa/payment/callback/test

# Réponse attendue :
{
  "success": true,
  "message": "Callback endpoint accessible",
  "timestamp": "2025-11-20 16:30:00"
}
```

**Si ça échoue :**
- Vérifiez que votre serveur est accessible depuis Internet
- Vérifiez votre configuration Nginx/Apache
- Vérifiez votre pare-feu

#### Test 2 : Commande de vérification manuelle

```bash
# Exécuter manuellement la commande
php artisan payments:check-pending

# Résultat attendu :
# ✓ Affiche les transactions vérifiées
# ✓ Crédite automatiquement les wallets
```

#### Test 3 : Vérifier les logs

```bash
# Voir les logs de vérification automatique
tail -f storage/logs/check-pending-transactions.log

# Voir les logs Laravel généraux
tail -f storage/logs/laravel.log | grep -i payplus
```

---

## 🔧 Correction immédiate de votre transaction actuelle

### Méthode 1 : Via la commande Artisan

```bash
# Créer un fichier .env à partir de .env.dev
cp .env.dev .env

# Exécuter la vérification
php artisan payments:check-pending

# Votre transaction devrait être automatiquement traitée !
```

### Méthode 2 : Via le script PHP direct

```bash
# Si la méthode 1 ne fonctionne pas
php fix_pending_transaction.php c871f3db-ad43-46c5-9338-fe55ec7786bf
```

### Méthode 3 : Manuellement en base de données

Si tout le reste échoue, vous pouvez créditer manuellement :

```sql
-- 1. Vérifier la transaction
SELECT * FROM payment_transactions
WHERE id = 'c871f3db-ad43-46c5-9338-fe55ec7786bf';

-- 2. Marquer comme complétée
UPDATE payment_transactions
SET status = 'COMPLETED',
    completed_at = NOW()
WHERE id = 'c871f3db-ad43-46c5-9338-fe55ec7786bf';

-- 3. Créditer le wallet
INSERT INTO wallet_transactions (id, user_id, amount, type, description, reference_id, created_at)
VALUES (
    gen_random_uuid(),
    '01b89be1-aa50-4720-a612-23f76cba0e60',
    100,
    'CREDIT',
    'Dépôt PayPlus - DEP-1763653652-c871f3db',
    'c871f3db-ad43-46c5-9338-fe55ec7786bf',
    NOW()
);

-- 4. Mettre à jour le solde du wallet
UPDATE wallets
SET balance = balance + 100
WHERE user_id = '01b89be1-aa50-4720-a612-23f76cba0e60';
```

---

## 📊 Monitoring et Maintenance

### Vérifier que tout fonctionne

```bash
# 1. Vérifier que le scheduler tourne
ps aux | grep "schedule:work"

# 2. Vérifier les derniers logs
tail -50 storage/logs/check-pending-transactions.log

# 3. Vérifier s'il y a des transactions en attente
php artisan payments:check-pending --limit=10
```

### Statistiques

```bash
# Voir combien de transactions sont en attente
php artisan tinker
>>> PaymentTransaction::where('status', 'PENDING')->count()

# Voir les transactions complétées aujourd'hui
>>> PaymentTransaction::where('status', 'COMPLETED')
    ->whereDate('completed_at', today())
    ->count()
```

---

## 🔍 Diagnostic approfondi

### Pourquoi le callback n'arrive pas ?

#### Cause 1 : URL pas accessible depuis Internet

**Test :**
```bash
# Depuis un autre serveur ou https://reqbin.com
curl -X POST https://app-dev.whatspay.africa/payment/callback/test-id \
  -H "Content-Type: application/json" \
  -d '{"test": true}'
```

**Solutions :**
- Vérifiez votre pare-feu
- Vérifiez que le port 80/443 est ouvert
- Vérifiez votre DNS

#### Cause 2 : Erreur 500 dans le callback

**Test :**
```bash
# Vérifier les logs d'erreur
grep "payment/callback" storage/logs/laravel.log | grep ERROR
```

**Solutions :**
- Regardez les logs pour l'erreur exacte
- Vérifiez les permissions de fichiers
- Vérifiez la connexion à la base de données

#### Cause 3 : PayPlus abandonne (timeout)

**Test :**
```bash
# Mesurer le temps de réponse
time curl https://app-dev.whatspay.africa/payment/callback/test
```

**Solutions :**
- Optimisez votre serveur
- Augmentez le timeout PHP (max_execution_time)
- Utilisez des queues pour les traitements longs

#### Cause 4 : HTTPS requis

PayPlus peut refuser d'envoyer des callbacks vers HTTP (non sécurisé).

**Solutions :**
- Installez un certificat SSL (Let's Encrypt gratuit)
- Configurez HTTPS sur votre serveur

---

## ✅ Checklist finale

Avant de considérer le problème résolu :

- [ ] Code mis à jour sur le serveur de production
- [ ] Scheduler Laravel activé (cron ou supervisor)
- [ ] Test endpoint `/payment/callback/test` accessible
- [ ] Commande `payments:check-pending` exécutée manuellement avec succès
- [ ] Transaction actuelle (`c871f3db...`) traitée et wallet crédité
- [ ] Logs vérifiés (pas d'erreurs)
- [ ] Nouveau dépôt de test réalisé avec succès
- [ ] Callback reçu automatiquement dans les 5 minutes

---

## 🆘 Support

Si le problème persiste :

### 1. Vérifiez les logs détaillés

```bash
# Logs PayPlus
grep -i "payplus" storage/logs/laravel.log | tail -100

# Logs callback
grep -i "callback" storage/logs/laravel.log | tail -50

# Logs de la commande de vérification
cat storage/logs/check-pending-transactions.log
```

### 2. Activez le mode debug

Dans `.env` :
```env
APP_DEBUG=true
PAYPLUS_LOGGING=true
LOG_LEVEL=debug
```

### 3. Testez en local

```bash
# Utiliser ngrok pour exposer votre serveur local
ngrok http 8000

# Mettre l'URL ngrok dans votre config PayPlus
# Exemple : https://abc123.ngrok.io/payment/callback/{transaction}
```

### 4. Contactez le support PayPlus

- Email : support@payplus.africa
- Demandez-leur de vérifier les logs de callback de leur côté
- Donnez-leur votre transaction ID et l'URL de callback

---

## 📝 Résumé

**Ce qui a été corrigé :**

1. ✅ Routes callback acceptent GET et POST
2. ✅ Système de vérification automatique toutes les 5 minutes
3. ✅ Endpoint de test pour diagnostic
4. ✅ Scripts pour corriger manuellement les transactions bloquées

**Résultat attendu :**

- Plus aucune transaction ne reste bloquée > 5 minutes
- Les wallets sont crédités automatiquement même si le callback échoue
- Monitoring facile via les logs

**Prochaines étapes :**

1. Déployer sur production
2. Activer le scheduler
3. Tester avec un nouveau dépôt
4. Surveiller les logs pendant 24h

Bonne chance ! 🚀
