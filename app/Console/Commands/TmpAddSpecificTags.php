<?php

namespace App\Console\Commands;

use App\Models\Tags\Genre;
use App\Models\Tags\Mood;
use App\Models\Tags\Tag;
use App\Models\Tags\Tagging;
use App\Models\Tags\TagPosition;
use App\Models\Tags\Type;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TmpAddSpecificTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:add-specific-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to add specific tags for tracks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected $morphTo = [
        'genre' => Genre::class,
        'usage-type' => Type::class,
        'mood' => Mood::class,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $trackMorphClass = (new Track())->getMorphClass();
        $mapping = [
            'genre' => 
                [
                    'Trap' => [70,71,298,281,573,72,21,187,159,16,248,293,112,869,865,837,832,825,804,799,763,738,403,613,559,520,442,430,402,382,376,356,239,879],
                    'Lo-Fi' => [386,157,328,486,514,532,551,790,792,820,856,857],
                ],
            'usage-type' => 
                [
                    'YouTube' => [311,938,495,689,659,795,881,578,113,202,197,167,230,227,290,867,649,629,870,856,658,847,819,690,830,820,942,700,914,501,532,716,551,559,260,207,257,255,229,288,242,282,699,805,807,717,703,761,798,813,778,704,829,910,538,567,568,569,570,571,931,930,579,582,610,628,674,905,894,892,889,884,630,641,707,855,284,840,835,415,9,306,608,713,860,652,497,877,904,906,921,611,602,390,597,596,586,922,576,355,933,516,364,853,368,851,483,772,404,468,693,477,687,480,678,810,295,450,59,48,60,178,49,174,276,22,326,165,285,111,278,946,469,509,514,464,943,492,944,448,945,709,941,801,734,756,773,701,780,782,786,679,833,560,848,852,861,869,613,929,939,940,562,417,947,303,383,21,68,35,94,53,136,243,106,168,102,157,103,46,83,132,145,17,169,71,86,192,112,280,264,256,40,24,28,90,26,65,552,804,239,555,865,438,558,549,842,370,362,837,735,838,359,712,732,812,591,573,546,421,430,715,334,822,424,825,540,826,763,314,391,392,488,846,486,764,758,928,408,402,768,461,769,542,919,529,908,511,445,667,443,523,907,525,527,605,790,799,850,909,537,72,36,181,52,58,154,124,226,171,238,163,211,234,162,151,73,93,54,127,161,206,187,221,149,182,190,120,158,159,252,240,296,16,212,271,177,199,77,176,253,214,292,135,91,134,219,299,277,31,88,324,96,309,308,328,310,327,330,320,797,913,796,912,800,911,125,915,803,903,902,806,901,900,808,899,898,811,802,793,794,925,937,936,781,935,934,932,783,784,926,785,896,923,920,787,918,788,917,789,916,791,792,897,893,814,864,875,874,872,834,836,839,868,866,841,832,863,843,862,844,845,859,858,857,849,876,831,815,887,816,817,818,895,854,821,891,890,888,886,878,823,824,885,883,882,880,879,827,828,779,413,777,575,584,583,581,580,336,343,345,574,587,572,350,357,360,361,564,372,585,588,373,625,645,644,639,637,634,631,627,624,593,621,620,617,615,609,604,598,329,553,547,647,455,403,467,405,460,458,457,456,452,478,407,444,440,439,437,436,431,419,401,398,545,382,539,375,535,380,531,530,528,513,396,386,503,387,499,388,494,490,487,646,648,776,736,743,742,741,740,739,738,737,409,746,733,730,729,728,727,726,725,724,744,747,722,760,775,774,771,770,767,765,762,759,748,757,755,754,753,752,751,750,749,723,721,651,666,675,673,672,671,670,669,668,665,677,664,663,662,661,660,656,654,653,676,680,718,695,714,710,708,706,702,698,697,694,681,692,691,688,686,685,684,683,682,642],
                    'Podcast' => [938,659,559,801,942,856,867,870,930,931,149,182,9,553,741,921,648,388,630,587,814,703,311,295,450,59,48,60,178,49,113,202,174,197,276,167,165,230,326,22,227,285,111,290,278,709,941,532,514,509,495,492,943,469,861,464,448,852,417,869,383,940,881,303,914,929,939,551,848,734,786,701,700,690,689,679,756,773,780,782,795,847,629,613,944,830,578,833,945,562,560,946,658,947,21,68,35,94,53,243,136,106,168,102,157,46,83,132,145,207,260,17,169,71,86,192,112,264,280,229,256,40,90,24,28,288,242,65,282,540,758,764,542,537,546,761,763,538,919,529,768,549,527,525,523,769,511,778,334,790,798,735,567,552,712,641,649,667,674,605,284,699,591,704,582,579,732,715,716,717,573,571,570,569,568,486,558,555,488,501,799,391,430,424,421,840,415,842,846,408,850,402,855,392,865,837,928,884,889,370,892,894,362,905,907,908,909,910,838,707,438,826,813,812,461,835,314,819,628,820,825,822,239,445,443,804,805,829,72,36,52,58,154,124,226,171,152,238,163,211,234,162,151,161,127,93,54,187,206,190,158,120,159,252,240,212,16,296,271,77,257,177,199,253,135,292,91,255,299,306,219,134,324,88,96,309,294,328,308,310,327,409,845,844,843,436,413,841,419,839,437,407,627,849,404,851,403,853,854,401,439,857,858,859,860,836,452,834,816,480,802,803,478,477,806,807,808,810,811,468,467,460,815,817,440,818,458,457,821,456,823,824,455,862,827,828,444,831,832,396,872,863,916,902,903,904,360,906,357,355,350,345,911,912,913,343,915,917,900,918,336,920,329,922,923,925,926,932,933,934,935,936,937,901,899,864,882,390,866,386,868,382,483,874,875,876,877,878,879,880,380,883,898,375,885,886,887,888,372,890,891,368,893,364,895,896,897,800,796,490,686,692,691,597,600,688,687,685,694,684,683,682,681,680,602,693,695,677,706,713,580,710,581,708,583,584,697,585,702,586,593,596,698,678,676,576,646,653,652,651,617,620,647,645,615,644,642,621,637,631,624,654,611,608,609,673,672,671,670,669,668,666,610,665,664,663,662,661,660,714,574,797,771,777,776,775,774,513,772,770,779,516,528,767,765,530,531,503,125,535,789,625,494,794,793,792,791,788,781,787,497,785,784,783,499,762,760,718,728,737,736,547,733,730,729,727,739,726,725,724,723,722,721,738,740,759,752,539,757,545,755,754,753,751,742,750,749,748,747,746,744,743,656],
                    'Commercials' => [47,45,943,942,946,941,146,35,118,94,48,102,18,24,65,43,939,944,404,938,947,940,323,68,36,181,59,238,49,150,235,46,83,190,210,207,202,16,209,292,302,192,197,326,230,306,299,134,112,283,229,40,285,227,290,111,26,310,804,771,516,501,500,492,598,813,835,851,529,401,884,892,480,905,303,403,407,764,753,759,353,368,609,629,659,361,689,908,357,373,699,595,700,354,594,370,351,736,574,348,347,573,737,341,571,746,553,538,751,358,462,51,909,912,286,917,921,926,413,30,204,124,226,136,152,25,175,41,116,117,39,19,60,263,211,115,168,244,161,178,205,98,187,269,270,149,155,232,182,221,113,132,195,260,217,158,129,17,212,196,296,271,199,77,174,189,253,180,208,135,246,198,191,10,219,280,184,297,245,264,321,279,294,96,313,282,318,331,295,312,325,281,769,772,770,768,767,773,412,778,795,796,797,322,806,810,815,817,819,820,801,758,765,749,715,717,732,735,349,343,741,340,747,339,763,752,338,754,755,756,757,825,336,760,761,824,840,826,918,895,897,900,904,305,911,914,915,916,922,891,923,925,929,931,933,934,935,239,125,894,890,829,846,830,832,833,837,838,712,842,844,845,315,889,855,860,862,863,865,868,877,879,880,882,713,709,411,519,396,394,503,504,509,511,512,513,515,391,517,523,495,528,390,531,535,536,387,539,540,542,544,546,496,494,549,460,415,425,428,430,431,433,445,451,454,455,459,461,397,409,463,466,468,472,473,476,477,402,398,489,547,551,708,362,615,617,627,628,363,630,639,641,643,646,649,667,364,680,360,690,693,694,696,356,355,701,704,707,613,608,552,570,383,554,557,559,560,561,562,565,567,568,569,382,601,572,380,379,577,579,580,582,585,376,375,372,483],
                    'Public Events' => [584,46,765,769,825,942,768,847,136,101,16,229,805,391,819,764,715,773,401,921,667,613,178,113,177,280,694,445,495,436,685,658,593,686,573,845,876,368,347,875,872,211,162,168,244,129,90,290,242,492,910,900,494,620,348,354,516,520,580,579,835,486,881,421,797,413,380,383,206,249,169,199,255,272,112,11,407,343,689,480,529,671,159,252,167,48,51,27,755,757,68,156,123,428,94,225,153,425,361,947,938,946,574,943,107,38,56,238,49,327,735,353,758,340,571,826,687,43,503,702,939,286,916,41,39,106,115,203,161,188,205,149,200,71,212,213,292,245,88,283,266,96,312,922,930,925,931,917,941,284,349,351,404,358,698,562,578,609,519,610,509,615,652,497,466,889,462,438,433,741,759,772,804,878,390,363,587,442,146,62,204,171,53,175,152,117,82,251,29,186,187,95,210,207,158,202,160,209,86,180,174,173,9,246,299,44,321,227,65,832,859,540,912,451,429,357,856,844,415,370,344,867,821,837,870,836,504,473,379,885,475,542,855,605,681,935,624,621,752,708,753,601,597,701,659,763,928,314,588,628,914,798,323,795,72,36,181,59,163,234,73,98,166,75,232,190,182,83,215,271,253,208,191,197,66,134,184,288,463,846,453,709,679,693,457,850,548,644,815,546,553,439,794,517,513,505,467,822,500,499,478,751,744,840,802,454,378,891,409,403,397,422,883,886,239,906,869,909,345,341,918,868,226,14,157,195,120,196,189,214,248,198,276,219,201,224,294,298,313,325,307,282,448,904,799,443,600,604,450,907,813,770,803,842,514,455,862,934,831,937,488,393,680,502,256,582,754,84,114,105,132,337,482,21,60,143,260,78,17,69,291,261,40,24,18,311,285,668,303,713,625,717,944,707,558,392,551,382,829,408,464,865,820,424,356,426,430,355,915,432,796,431,778,782,220,58,50,116,19,237,103,37,231,267,257,192,91,230,265,309,111,304,316,278,26,281,908,699,793,33,761,756,700,801,810,812,879,716,892,703,704,887,750,919,830,734,712,725,843,576,470,560,589,586,388,305,570,569,437,568,565,338,594,532,447,398,460,461,396,376,377,394,484,411,575,648,627,645,641,662,412,669,670,623,32,92,30,130,258,85,131,20,268,263,142,144,64,151,70,127,93,269,218,221,217,57,273,228,259,15,77,63,176,126,302,326,216,254,277,264,330,289,331,310,410,405,874,402,317,387,359,936,933,932,315,864,911,360,385,362,372,373,898,894,381,417,695,418,767,533,537,547,549,559,563,790,781,777,572,577,581,528,591,595,608,637,738,726,656,661,663,674,678,684,531,524,861,479,420,858,857,427,854,852,444,446,452,468,469,472,692,811,481,490,498,827,501,507,508,816,511,814,512,518,833],
                    'Social media videos' => [881,795,659,578,819,700,856,641,629,820,567,569,923,570,870,884,571,889,813,582,716,717,699,17,282,798,687,549,678,840,674,306,404,666,860,877,737,364,652,713,52,311,938,701,495,734,689,113,202,591,861,145,197,230,167,22,227,290,830,501,756,658,833,801,841,448,847,848,551,559,946,942,649,690,724,914,532,782,867,542,679,712,106,102,46,132,207,260,257,192,255,229,28,288,242,26,807,239,805,799,552,695,537,778,284,783,415,761,812,703,715,579,894,892,905,910,707,704,865,749,610,919,568,855,628,928,630,760,930,835,829,823,931,538,93,206,182,190,9,405,608,468,477,583,586,372,710,480,708,596,483,597,602,381,611,407,624,625,631,564,647,656,497,388,390,697,662,669,694,693,576,516,368,857,791,792,802,810,827,831,843,851,918,853,722,912,921,906,858,863,864,868,904,876,879,890,891,900,896,789,788,775,355,723,727,736,739,746,753,767,771,922,772,777,784,787,933,895,779,76,541,45,301,295,450,766,59,48,60,49,178,105,174,247,276,165,326,279,285,111,278,773,945,944,943,709,941,940,522,780,939,869,560,562,929,852,613,786,947,383,492,469,464,509,514,303,417,21,68,35,94,204,67,53,243,136,223,168,47,235,157,194,103,83,78,169,71,86,112,280,264,40,256,90,24,55,65,408,314,768,667,873,825,850,424,430,769,636,790,438,443,445,421,763,764,838,826,822,732,359,735,370,837,334,402,804,842,523,758,391,392,846,362,414,907,529,601,546,558,491,908,555,909,527,489,540,525,573,544,511,605,486,461,488,72,36,181,58,154,124,171,226,238,163,211,263,148,234,151,73,162,54,127,161,186,187,155,221,149,159,158,120,240,252,296,89,212,16,271,259,177,267,77,199,214,253,176,91,135,292,219,299,134,122,277,324,265,283,88,31,96,309,298,275,328,308,307,327,320,330,316,310,811,935,808,793,803,794,806,936,797,800,937,796,834,814,882,862,866,872,903,874,875,902,878,880,883,854,885,886,887,901,888,899,125,898,893,859,911,934,925,815,932,816,817,818,821,824,828,926,832,849,897,836,839,920,917,916,915,844,845,913,518,754,785,617,627,626,444,621,620,618,615,439,446,451,452,609,455,607,604,440,634,598,435,419,661,660,654,653,431,651,648,637,436,646,645,644,642,437,639,456,457,664,539,499,503,507,548,547,545,513,494,536,535,533,515,531,530,528,553,490,458,462,595,593,460,588,587,585,584,581,487,580,467,475,575,574,572,478,663,665,286,730,741,740,738,336,343,733,729,743,728,345,726,725,349,350,357,742,744,718,322,287,781,776,774,770,765,762,759,329,757,755,752,751,750,748,747,721,360,668,409,685,684,683,682,681,680,677,403,676,675,413,673,672,671,670,686,688,361,382,714,373,374,375,377,706,380,702,401,386,387,698,396,398,692,691,600],
                    'Website' => [238,306,926,83,45,65,938,59,271,815,171,192,321,40,943,370,947,363,758,464,124,253,292,313,310,323,610,765,917,480,769,931,379,764,92,211,244,168,161,93,166,212,177,255,679,408,701,407,393,925,392,424,450,381,529,549,768,429,735,85,163,206,129,257,91,307,391,876,872,401,396,436,388,404,537,819,689,725,376,180,216,444,373,922,770,771,911,362,516,486,898,900,38,904,867,870,327,582,757,181,146,35,94,53,175,258,39,84,47,102,132,158,71,300,326,325,568,814,577,545,584,587,825,385,786,613,419,773,942,707,940,685,930,939,21,36,154,204,226,101,60,162,49,54,178,46,103,221,190,260,17,153,296,86,174,299,297,280,285,311,55,242,312,509,837,833,503,847,751,732,489,454,746,729,462,868,469,865,445,538,832,560,658,778,782,763,602,797,667,805,760,573,759,558,753,820,696,551,547,542,539,535,528,525,523,829,518,562,944,890,354,383,315,314,380,934,916,915,741,413,946,421,368,356,303,72,30,27,136,251,187,194,113,207,200,202,169,209,213,197,134,230,112,22,88,309,24,227,308,111,288,290,278,646,752,700,702,704,708,239,715,609,756,750,793,604,747,921,734,919,699,694,645,630,597,644,929,662,935,669,628,617,284,761,675,621,686,779,687,798,879,801,494,519,515,830,513,593,843,844,384,851,855,357,856,862,476,471,394,889,461,869,883,908,838,532,821,804,588,585,438,810,812,561,351,557,552,341,439,345,909,822,347,52,107,104,117,19,106,20,234,73,64,127,98,156,157,155,195,159,249,236,160,252,16,199,123,9,173,248,276,167,272,11,264,229,266,324,294,90,298,275,274,295,222,282,443,878,875,467,446,448,442,452,453,738,468,863,477,858,857,478,433,492,495,850,881,361,885,349,933,928,286,918,322,343,913,348,910,358,886,907,846,903,372,899,397,891,402,403,496,497,845,652,608,785,611,781,780,620,624,625,626,629,639,643,767,663,605,666,671,680,681,682,693,695,714,718,721,722,749,723,790,794,733,546,499,842,840,501,507,835,512,831,514,520,827,531,540,554,599,569,817,570,574,813,579,811,580,581,591,595,799,598,511],
                ],
            'mood' => [
                'Feel Good' => [943,238,63,215,569,571,876,39,862,872,454,428,266,946,38,14,15,11,327,930,947,85,46,916,422,875,397,124,163,232,326,935,925,940,562,391,178,195,219,313,360,407,348,315,926,135,255,508,879,555,894,376,868,613,56,48,251,153,51,27,171,156,103,180,497,941,735,755,43,401,36,68,59,175,117,73,166,221,200,123,176,292,66,321,312,499,353,425,479,939,340,844,698,466,286,887,457,922,361,878,403,152,115,64,244,225,149,158,160,271,209,253,112,184,40,96,65,330,383,778,764,751,945,390,542,707,708,769,558,906,379,473,462,358,652,504,505,917,931,819,568,433,621,343,847,560,540,605,162,205,206,187,83,182,189,191,198,254,245,201,229,274,282,500,463,584,578,495,494,455,486,475,439,445,363,570,368,370,436,420,415,529,404,548,490,772,842,658,825,239,795,890,914,715,704,667,773,870,641,323,336,226,243,211,84,168,203,29,95,190,129,249,252,169,196,86,177,214,126,167,224,90,242,290,325,310,331,516,492,502,874,910,469,604,517,547,620,579,322,671,303,421,488,553,393,813,833,409,413,587,528,417,416,314,45,938,94,741,942,146,53,236,42,895,615,694,659,746,758,765,768,771,458,574,687,204,136,41,223,101,47,210,16,69,22,44,28,288,295,580,880,701,576,645,593,693,886,884,329,867,689,601,349,835,610,597,437,859,815,840,478,476,471,467,826,824,821,514,805,375,804,794,527,545,753,385,912,850,856,905,845,181,62,104,258,82,76,127,98,188,186,157,99,75,132,113,159,120,212,89,259,77,267,199,91,246,208,276,306,272,137,280,264,283,241,265,279,227,304,328,233,307,222,921,920,909,852,907,904,934,900,893,860,861,883,881,911,487,836,532,567,377,378,380,381,382,551,386,544,392,408,536,414,362,520,424,440,515,512,510,443,507,448,451,496,493,480,364,573,831,678,797,789,744,491,721,718,709,287,699,695,686,685,679,305,577,668,644,637,335,344,345,347,600,590,351,354,357,359,730],
            ],
        ];
        
        foreach($mapping as $tagKey => $data) {
            $morphTo = $this->morphTo[$tagKey];
            foreach ($data as $tagName => $trackIds) {
                $tagId = $this->createTagIfNotExists($morphTo, $tagName);

                $parameters = [
                    'object_type' => $trackMorphClass,
                    'tag_type' => $morphTo,
                    'tag_id' => $tagId,
                ];

                $wasCreated = 0;
                $existed = 0;
                $count = count($trackIds);

                $this->output->section("Adding {$tagKey}:{$tagName} to {$count} tracks");
                $this->withProgressBar($trackIds, function ($trackId) use ($parameters, &$wasCreated, &$existed) {
                    $parameters['object_id'] = $trackId;
                    $result = Tagging::updateOrCreate($parameters);
                    $wasCreated = $wasCreated + ($result->wasRecentlyCreated ? 1 : 0);
                    $existed = $existed + (count($result->getChanges()) === 0 ? 1 : 0);
                });

                $this->output->table(['created', 'existed'], [[$wasCreated, $existed]]);
            }
        }

        return 0;
    }

    private function createTagIfNotExists(string $morphTo, string $tagName)
    {
        $tagNameSlug = Str::slug($tagName);

        /**
         * @var $model Tag
         */
        $model = resolve($morphTo);
        $tag = $model::firstOrCreate([
            'slug' => $tagNameSlug,
        ], [
            'name' => $tagName,
        ]);

        if ($tag->wasRecentlyCreated) {
            $dataToFind = [
                'taggable_type' => $tag->getMorphClass(),
                'taggable_id' => $tag->id,
            ];

            $result = TagPosition::updateOrCreate($dataToFind, [
                'position' => 100,
            ]);
        }

        $tag->flushCache();

        return $tag->id;
    }
}
