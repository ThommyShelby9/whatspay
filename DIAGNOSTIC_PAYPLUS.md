# 🔍 Guide de Diagnostic - Erreur PayPlus Code '01'

## Problème

Lorsque vous tentez de faire un dépôt, vous recevez le **code d'erreur 01** de PayPlus.

## Qu'est-ce que le code '01' ?

Le code d'erreur **'01'** signifie que PayPlus a **refusé la transaction**. Les causes possibles sont :

### 1. 🔑 Problèmes d'authentification
- API Key invalide ou expirée
- API Token invalide ou expiré
- Compte PayPlus désactivé ou suspendu

### 2. 📱 Problème de numéro de téléphone
- Format incorrect (manque l'indicatif pays)
- Opérateur mobile money non supporté
- Numéro non valide ou désactivé

### 3. 💰 Problème de montant
- Montant trop faible (< minimum autorisé)
- Montant trop élevé (> maximum autorisé)
- Montant invalide (négatif, null, etc.)

### 4. ⚙️ Problèmes de configuration
- Opérateurs mobile money non activés sur votre compte PayPlus
- Webhooks/callbacks mal configurés
- Compte en mode test vs production

---

## 🛠️ Comment résoudre

### Étape 1 : Modifier le numéro de test

Avant de lancer le diagnostic, modifiez le numéro de téléphone dans le script :

```bash
# Ouvrir le fichier
notepad test_payplus_deposit.php

# Modifier cette ligne (ligne 30) :
$testPhone = '22997000000'; // ← Mettez VOTRE vrai numéro ici !
```

**Format attendu :** `229XXXXXXXX` (indicatif pays + numéro)

Exemples :
- Bénin (229) : `22997000000`
- Côte d'Ivoire (225) : `2250708000000`
- Togo (228) : `22890000000`

### Étape 2 : Exécuter le script de diagnostic

```bash
php test_payplus_deposit.php
```

Le script va :
1. ✓ Vérifier votre configuration PayPlus
2. ✓ Tester une vraie requête vers l'API PayPlus
3. ✓ Afficher le code d'erreur exact et la description
4. ✓ Donner des recommandations spécifiques

### Étape 3 : Analyser le résultat

#### Si vous voyez "✓ SUCCÈS!"
Votre configuration fonctionne ! Le problème vient peut-être d'autre chose (voir Cas spéciaux ci-dessous).

#### Si vous voyez "✗ ÉCHEC - Code d'erreur: 01"
Lisez attentivement la **description de l'erreur** retournée par PayPlus. Elle vous dira exactement quel est le problème.

Exemples de descriptions possibles :
- `"Invalid API credentials"` → Vos credentials sont incorrects
- `"Operator not activated"` → L'opérateur mobile money n'est pas activé
- `"Invalid phone number"` → Le numéro de téléphone est mal formaté
- `"Amount below minimum"` → Le montant est trop faible

---

## 🔧 Solutions selon le problème

### Problème 1 : Credentials invalides

**Symptôme :** `"Invalid API credentials"` ou `"Unauthorized"`

**Solution :**

1. Connectez-vous sur [https://app.payplus.africa](https://app.payplus.africa)
2. Allez dans **Settings** → **API**
3. Copiez votre **API Key** et **API Token**
4. Mettez à jour votre fichier `.env` :

```env
PAYPLUS_API_KEY=votre_vraie_api_key
PAYPLUS_API_TOKEN=votre_vrai_token
```

5. Effacez le cache Laravel :

```bash
php artisan config:clear
php artisan cache:clear
```

### Problème 2 : Opérateur non activé

**Symptôme :** `"Operator not activated"` ou `"Network not available"`

**Solution :**

1. Connectez-vous sur PayPlus
2. Allez dans **Settings** → **Payment Methods**
3. Activez les opérateurs souhaités :
   - ✓ MTN Mobile Money
   - ✓ Moov Money
   - ✓ Autres opérateurs disponibles

4. Configurez les limites de montant pour chaque opérateur

### Problème 3 : Numéro de téléphone invalide

**Symptôme :** `"Invalid phone number"` ou `"Customer not found"`

**Solutions :**

1. **Vérifiez le format :** Le numéro doit inclure l'indicatif pays (229 pour Bénin)
   - ✓ Correct : `22997000000`
   - ✗ Incorrect : `97000000`
   - ✗ Incorrect : `+229 97 00 00 00`

2. **Vérifiez que le numéro est actif** et peut recevoir des paiements mobile money

3. Si le problème persiste, modifiez le code de formatage dans `PaymentService.php` (lignes 145-160)

### Problème 4 : Montant invalide

**Symptôme :** `"Amount below minimum"` ou `"Amount exceeds maximum"`

**Solution :**

Vérifiez les limites configurées sur PayPlus :
- Minimum : généralement 100 FCFA
- Maximum : généralement 1 000 000 FCFA

Mettez à jour votre `.env` :

```env
PAYPLUS_MIN_DEPOSIT=100
PAYPLUS_MAX_DEPOSIT=1000000
```

---

## 📋 Cas spéciaux

### Le diagnostic réussit mais l'app échoue toujours

Vérifiez :

1. **La session utilisateur** : Vérifiez que `$userId` est correct dans les logs
2. **Les routes** : Vérifiez que les routes de callback sont accessibles
3. **Le pare-feu** : Vérifiez que PayPlus peut accéder à vos callbacks

### Erreur seulement pour certains numéros

Le problème vient probablement de :
- L'opérateur de ce numéro n'est pas activé sur PayPlus
- Le numéro est dans une liste noire/limite

### Le dépôt fonctionne en test mais pas en production

Vérifiez :
1. Que vous utilisez les credentials **production** (pas test)
2. Que votre compte PayPlus est **vérifié** et **activé** pour la production
3. Que les URL de callback sont accessibles depuis internet (pas localhost)

---

## 📞 Support

Si le problème persiste après toutes ces étapes :

### Contactez PayPlus

- **Email :** support@payplus.africa
- **Téléphone :** Vérifiez sur leur site web

**Informations à fournir :**
- Votre API Key (premiers caractères seulement)
- Le code d'erreur reçu (01)
- La description complète de l'erreur
- Un exemple de payload envoyé (sans données sensibles)

### Logs à vérifier

Pour avoir plus de détails, consultez :

```bash
# Logs Laravel
tail -100 storage/logs/laravel.log

# Filtrer les logs PayPlus
grep -i "payplus" storage/logs/laravel.log | tail -50

# Voir les dernières erreurs
grep -i "erreur\|error\|échec\|failed" storage/logs/laravel.log | tail -30
```

---

## ✅ Checklist finale

Avant de contacter le support, vérifiez :

- [ ] API Key et Token sont corrects et à jour
- [ ] Compte PayPlus est actif (pas suspendu)
- [ ] Opérateurs mobile money sont activés
- [ ] Format du numéro de téléphone est correct (avec indicatif pays)
- [ ] Montant est dans les limites autorisées
- [ ] Le script de diagnostic a été exécuté
- [ ] Les logs ont été consultés
- [ ] Cache Laravel a été effacé (`php artisan config:clear`)

---

## 🎯 Résumé rapide

```bash
# 1. Modifier le numéro de test
notepad test_payplus_deposit.php

# 2. Exécuter le diagnostic
php test_payplus_deposit.php

# 3. Lire la description de l'erreur

# 4. Appliquer la solution correspondante

# 5. Effacer le cache
php artisan config:clear

# 6. Retester
php test_payplus_deposit.php
```

Bonne chance ! 🚀
