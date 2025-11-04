<?php

namespace Database\Seeders;

use App\Consts\Util;
use App\Models\Category;
use App\Models\Contenttype;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Deptnumber;
use App\Models\Lang;
use App\Models\Locality;
use App\Models\Merchant;
use App\Models\MerchantNetwork;
use App\Models\Network;
use App\Models\Occupation;
use App\Models\Participation;
use App\Models\Provider;
use App\Models\Right;
use App\Models\Role;
use App\Models\Study;
use App\Models\Tranche;
use App\Models\User;
use App\Traits\Utils;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Event\Runtime\PHP;

class TablesSeeder extends Seeder
{
  
    use Utils;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $roles = Role::All();
        $date = gmdate('Y-m-d H:i:s');
        // Juste après $roles = Role::All();
        $admin_role = "";
        // Essayer de trouver le rôle ADMIN s'il existe déjà
        foreach ($roles as $role) {
            if ($role->typerole == "ADMIN") {
                $admin_role = $role->id;
                break;
            }
        }
        if(count($roles) == 0){
            echo 'seeding roles'.PHP_EOL;
            foreach (Util::TYPES_ROLE as $key => $value){
                $rId = $this->getId();
                Role::create([
                    'id' => $rId,
                    'name' => 'PROFIL '.$key,
                    'typerole' => $key,
                    'enabled' => true
                ]);
                if($key == "ADMIN"){
                    $admin_role = $rId;
                }
                foreach (Util::RIGHTS as $right){
                    if($right["typerole"] == $key) {
                        $rolerights = [
                            'role_id' => $rId,
                            'right' => $right["code"],
                            'updated_at' => $date,
                            'created_at' => $date,
                        ];
                        DB::table('rolerights')->insert($rolerights);
                    }
                }
            }
        }

        $users = User::All();
        if(count($users) == 0){
            echo 'seeding super admin account'.PHP_EOL;
            $uId = $this->getId();
            User::create([
                'id' => $uId,
                'email' => 'admin@lapieuvre.tech',
                'firstname' => 'Super',
                'lastname' => 'ADMIN',
                'password' => Hash::make('P@ssw0rd2024'),
                'email_verified_at' => $date,
                'enabled' => true
            ]);
            $role_user = [
              'role_id' => $admin_role,
              'user_id' => $uId,
              'updated_at' => $date,
              'created_at' => $date,
            ];
            DB::table('role_user')->insert($role_user);
        }

        $categories = Category::all();
        if(count($categories) == 0){
          echo 'seeding categories'.PHP_EOL;
          $categories = [
            "Électronique : Télévisions, téléphones, ordinateurs",
            "Mode : Vêtements, chaussures, accessoires",
            "Alimentation et boissons : Produits frais, épicerie, boissons",
            "Santé et beauté : Produits cosmétiques, soins personnels",
            "Maison et ameublement : Mobilier, articles de décoration"
          ];
          $categories = [
            'MODE & BEAUTE : vêtements, maquillage, soins de la peau, parfums, accessoires',
            'TECHNOLOGIE & MOBILE : smartphones, applications, IA, jeux mobiles, objets connectés',
            'FINANCE & BANQUE : mobile money, fintech, crypto, épargne, microcrédit',
            'ALIMENTATION & BOISSONS : fast-food, thé/café, boissons énergétiques, produits bio, snacks',
            'VOYAGES & TOURISME : hôtels, vols low-cost, destinations africaines, croisières, road trip',
            'EDUCATION & FORMATION : cours en ligne, langues, développement personnel, examens, coaching scolaire',
            'SPORT & FITNESS : football, basketball, musculation, yoga, courses/marathons',
            'AUTOMOBILE & TRANSPORTS : voitures électriques, motos, covoiturage, pièces auto, taxis/vtc',
            'MUSIQUE & DIVERTISSEMENT : concerts, streaming musical, DJ, festivals, karaoké',
            'CINEMA & SERIES : Netflix, films africains, cinéma d’action, films romantiques, science-fiction',
            'IMMOBILIER : achat, location, immobilier de luxe, colocation, immobilier commercial',
            'ENTREPREUNARIAT & BUSINESS : start-up, e-commerce, import-export, marketing digital, BB',
            'SANTE & BIEN-ETRE : nutrition, compléments alimentaires, méditation, fitness, soins naturels',
            'GAMING : e-sport, PlayStation, jeux mobiles, PC gaming, streaming Twitch',
            'CULTURE & ART : peinture, photographie, littérature, danse, mode africaine',
            'ANIMAUX & NATURE : animaux domestiques, vétérinaires, zoo, randonnées, écotourisme',
            'EVENEMENTS & VIE SOCIALE : mariages, anniversaires, soirées, festivals, networking',
            'POLITIQUE & SOCIETE : gouvernance, jeunesse, ONG, débats citoyens, associations',
            'MAISON & DECORATION : meubles, électroménager, bricolage, jardinage, domotique',
            'RELIGION & SPIRITUALITE : christianisme, islam, églises évangéliques, spiritualité africaine, développement spirituel'
          ];
          foreach($categories as $category){
            $cId = $this->getId();
            Category::create([
              'id' => $cId,
              'name' => $category,
              'enabled' => true
            ]);
          }
        }


        $langs = Lang::all();
        if(count($langs) == 0){
          echo 'seeding langs'.PHP_EOL;
          $langs = ['FRANCAIS', 'ANGLAIS', 'ARABE', 'CHINOIS', 'LANGUES LATINES', 'LANGUES LOCALES'];
          foreach($langs as $lang){
            $lId = $this->getId();
            Lang::create([
              'id' => $lId,
              'name' => $lang,
              'enabled' => true
            ]);
          }
        }

        $studies = Study::all();
        if(count($studies) == 0){
          echo 'seeding studies'.PHP_EOL;
          $studies = ['NON SCOLARISE','NIVEAU PRIMAIRE (CI -> CM2)','CEP','NIVEAU SECONDAIRE 01 (6eme -> 3eme)','BEPC','NIVEAU SECONDAIRE 02 (2nd -> Terminale)','BAC', 'LICENCE', 'MAITRISE', 'MASTER', 'DOCTORAT'];
          foreach($studies as $study){
            $sId = $this->getId();
            Study::create([
              'id' => $sId,
              'name' => $study,
              'enabled' => true
            ]);
          }
        }

        $contenttypes = Contenttype::all();
        if(count($contenttypes) == 0){
          echo 'seeding contenttypes'.PHP_EOL;
          $contenttypes = ["DIVERTISSANT", "EDUCATIF", "INFORMATIF", "HUMORISTIQUE", "MOTIVATIONNEL"];
          foreach($contenttypes as $contenttype){
            $cId = $this->getId();
            Contenttype::create([
              'id' => $cId,
              'name' => $contenttype,
              'enabled' => true
            ]);
          }
        }

        $occupations = Occupation::all();
        if(count($occupations) == 0){
          echo 'seeding occupations'.PHP_EOL;
          $occupations = ['Entrepreneur / Chef d’entreprise','Commerçant','Étudiant','Influenceur / Créateur de contenu','Employé de bureau','Enseignant / Professeur','Agent de banque / Financier','Artisan (menuisier, soudeur, plombier, etc.)','Chauffeur / Transporteur','Agriculteur / Éleveur','Médecin / Infirmier','Avocat / Juriste','Ingénieur (électricité, informatique, génie civil, etc.)','Développeur web / Informaticien','Community Manager / Marketeur digital','Photographe / Vidéaste','Artiste / Musicien / Acteur','Fonctionnaire','Ménagère','Étudiant entrepreneur','Coiffeur / Esthéticien','Couturier / Styliste','Restaurateur / Cuisinier','Agent immobilier','Technicien / Électricien','Journaliste / Animateur','Consultant / Coach','Comptable / Auditeur','Agent commercial / Représentant','Agent de santé communautaire'];
          foreach($occupations as $occupation){
            $oId = $this->getId();
            Occupation::create([
              'id' => $oId,
              'name' => $occupation,
              'enabled' => true
            ]);
          }
        }

        $localities = DB::select("select * from localities where country_id is null");
        if(count($localities) != 0){
          echo 'seeding localities'.PHP_EOL;
          $countries = Country::all();
          $bjId= "";
          foreach ($countries as $country) {
            if(strtoupper($country->iso2) == "BJ"){
              $bjId = $country->id; break;
            }
          }
          if(!empty($bjId)){
            DB::beginTransaction();
            Locality::where('id','>',1)->update([
              'country_id' => $bjId
            ]);
            DB::commit();
          }
        }

      echo 'seeding ends'.PHP_EOL;
    }

}
