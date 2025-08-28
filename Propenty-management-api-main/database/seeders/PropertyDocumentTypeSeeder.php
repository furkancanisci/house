<?php

namespace Database\Seeders;

use App\Models\PropertyDocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to prevent duplicates
        PropertyDocumentType::truncate();
        
        $documentTypes = [
            [
                'name_ar' => 'الطابو العقاري (الطابو العادي)',
                'name_en' => 'Real Estate Tabu (Standard Title)',
                'name_ku' => 'Tabuya Xanîyan (Navnîşana Asayî)',
                'description_ar' => 'الطابو العقاري العادي للممتلكات السكنية والتجارية',
                'description_en' => 'Standard registered title for residential and commercial properties',
                'description_ku' => 'Tabuya xanîyan a asayî ji bo malên nîcînî û bazirganî',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name_ar' => 'الطابو العيني (الطابو المُحدَّث)',
                'name_en' => 'Updated Tabu (Modernized Title)',
                'name_ku' => 'Tabuya Nûjen (Navnîşana Modern)',
                'description_ar' => 'طابو محدث ومطوَّر للعقارات المسجَّلة حديثًا',
                'description_en' => 'Modernized title used for newly registered or modernized properties',
                'description_ku' => 'Tabuya nûjen û pêşkeftî ji bo xanîyên ku nû hatine tomarkirin',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name_ar' => 'الطابو الأخضر (الطابو الزراعي)',
                'name_en' => 'Green Tabu (Agricultural Title)',
                'name_ku' => 'Tabuya Kesk (Navnîşana Çandînî)',
                'description_ar' => 'طابو يخص الأراضي الزراعية والحقول',
                'description_en' => 'Agricultural title for farmland and agricultural fields',
                'description_ku' => 'Tabuya çandînî ji bo erdên çandînî û zevîyan',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name_ar' => 'أراضي البناء (طابو البناء)',
                'name_en' => 'Construction Land (Building Title)',
                'name_ku' => 'Erdên Avahîsazî (Navnîşana Avahîsazî)',
                'description_ar' => 'طابو خاص بالأراضي المخصَّصة للبناء والتطوير',
                'description_en' => 'Special title for land designated for construction and development',
                'description_ku' => 'Tabuya taybet ji bo erdên ku hatine tayînkirin bo avahîsazî û pêşkeftin',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name_ar' => 'الطابو المؤقت',
                'name_en' => 'Temporary Tabu',
                'name_ku' => 'Tabuya Demkî',
                'description_ar' => 'طابو مؤقت قيد الانتظار حتى إتمام التسوية النهائية',
                'description_en' => 'Temporary title pending final settlement or completion procedures',
                'description_ku' => 'Tabuya demkî li bendê şertên çareserkirinê an temamkirinê',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name_ar' => 'الطابو العائلي (طابو مشترك)',
                'name_en' => 'Family Tabu (Shared Title)',
                'name_ku' => 'Tabuya Malbatî (Tabuya Hevpar)',
                'description_ar' => 'طابو مشترك يخص العائلة أو الورثة (ملكية بالشيوع أو حصص)',
                'description_en' => 'Shared family or inheritance title (co-ownership or divided shares)',
                'description_ku' => 'Tabuya hevpar a malbatî an mîrâtî (xwedîtiya bi hev an beşên parçe-parçe)',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name_ar' => 'قيود (رهن/حجز/حظر نقل)',
                'name_en' => 'Encumbrances (Mortgage / Seizure / Transfer Restriction)',
                'name_ku' => 'Tewerî (Rehn / Girtin / Sinorkirina Veguhastinê)',
                'description_ar' => 'قيود قانونية مسجلة على العقار مثل رهن بنكي أو حجز قضائي أو قيود نقل',
                'description_en' => 'Registered legal encumbrances on the property such as bank mortgage, judicial seizure, or transfer restrictions',
                'description_ku' => 'Tewerî yên qanûnî yên qeydkirî li ser xanî wek rehnê bankî an girtina dadgehî an sinorên veguhastinê',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name_ar' => 'عقار بحكم المحكمة',
                'name_en' => 'Court-Judgment Title',
                'name_ku' => 'Xanîya Bi Hûkmeta Dadgehê',
                'description_ar' => 'عقار ثبتت ملكيته أو حق تسجيله بحكم قضائي نهائي',
                'description_en' => 'Property title established or validated by a final court judgment',
                'description_ku' => 'Xanîyek ku xwedîtiyê wê bi hukmê dadgehê hate pejirandin',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name_ar' => 'ملكية بموجب وكالة موثقة',
                'name_en' => 'Title by Notarized Power of Attorney',
                'name_ku' => 'Xwedîtiyê Bi Wekîla Nasnameya Noterî',
                'description_ar' => 'ملكية مثبتة بوثيقة وكالة رسمية (مثل وكالة كاتب العدل)',
                'description_en' => 'Ownership evidenced by a notarized power of attorney (e.g. notarized agency)',
                'description_ku' => 'Xwedîtiyek ku bi belgeya vekîlatê noterî tê belgekirin',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name_ar' => 'أملاك الدولة / الأراضي الأميرية',
                'name_en' => 'State-Owned / Imperial Lands',
                'name_ku' => 'Mala Dewletê / Erdên Emîrî',
                'description_ar' => 'أراضي مملوكة للدولة أو ذات وضع خاص تخضع لقواعد تسجيل مغايرة',
                'description_en' => 'State-owned lands or special-status lands subject to different registration rules',
                'description_ku' => 'Erdên ku xwedîtiya wan dewlet e an heyeta taybet heye û rêbazên qeydê yên cuda hene',
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($documentTypes as $index => $type) {
            try {
                $created = PropertyDocumentType::create($type);
            } catch (\Exception $e) {
                $this->command->error("Failed to create document type: {$e->getMessage()}");
            }
        }
    }
}