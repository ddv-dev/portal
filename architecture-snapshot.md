Дата: 2026-10-05 12:50

## Структура app/
```
app
├── Auth
│   └── SsoGuard.php
├── Console
│   └── Commands
├── Filament
│   └── Resources
│       ├── AttachmentResource
│       │   └── Pages
│       │       ├── CreateAttachment.php
│       │       ├── EditAttachment.php
│       │       └── ListAttachments.php
│       ├── AttachmentResource.php
│       ├── BuildingResource
│       │   └── Pages
│       │       ├── CreateBuilding.php
│       │       ├── EditBuilding.php
│       │       └── ListBuildings.php
│       ├── BuildingResource.php
│       ├── CorpusDeptResource
│       │   └── Pages
│       │       ├── CreateCorpusDept.php
│       │       ├── EditCorpusDept.php
│       │       └── ListCorpusDepts.php
│       ├── CorpusDeptResource.php
│       ├── CorpusResource
│       │   └── Pages
│       │       ├── CreateCorpus.php
│       │       ├── EditCorpus.php
│       │       └── ListCorpuses.php
│       ├── CorpusResource.php
│       ├── DeptResource
│       │   └── Pages
│       │       ├── CreateDept.php
│       │       ├── EditDept.php
│       │       └── ListDepts.php
│       ├── DeptResource.php
│       ├── DeptTypeResource
│       │   └── Pages
│       │       ├── CreateDeptType.php
│       │       ├── EditDeptType.php
│       │       └── ListDeptTypes.php
│       ├── DeptTypeResource.php
│       ├── DeptWorkerResource
│       │   └── Pages
│       │       ├── CreateDeptWorker.php
│       │       ├── EditDeptWorker.php
│       │       └── ListDeptWorkers.php
│       ├── DeptWorkerResource.php
│       ├── InfoResource
│       │   └── Pages
│       │       ├── CreateInfo.php
│       │       ├── EditInfo.php
│       │       └── ListInfo.php
│       ├── InfoResource.php
│       ├── InfoTypeResource
│       │   └── Pages
│       │       ├── CreateInfoType.php
│       │       ├── EditInfoType.php
│       │       └── ListInfoTypes.php
│       ├── InfoTypeResource.php
│       ├── NewsResource
│       │   └── Pages
│       │       ├── CreateNews.php
│       │       ├── EditNews.php
│       │       └── ListNews.php
│       ├── NewsResource.php
│       ├── OrderStorageResource
│       │   └── Pages
│       │       ├── CreateOrderStorage.php
│       │       ├── EditOrderStorage.php
│       │       └── ListOrderStorages.php
│       ├── OrderStorageResource.php
│       ├── PhoneResource
│       │   └── Pages
│       │       ├── CreatePhone.php
│       │       ├── EditPhone.php
│       │       └── ListPhones.php
│       ├── PhoneResource.php
│       ├── SopResource
│       │   └── Pages
│       │       ├── CreateSop.php
│       │       ├── EditSop.php
│       │       └── ListSops.php
│       ├── SopResource.php
│       ├── WorkerPhoneResource
│       │   └── Pages
│       │       ├── CreateWorkerPhone.php
│       │       ├── EditWorkerPhone.php
│       │       └── ListWorkerPhones.php
│       ├── WorkerPhoneResource.php
│       ├── WorkerResource
│       │   └── Pages
│       │       ├── CreateWorker.php
│       │       ├── EditWorker.php
│       │       └── ListWorkers.php
│       └── WorkerResource.php
├── Http
│   ├── Controllers
│   │   ├── AttachmentController.php
│   │   ├── AuthController.php
│   │   ├── BirthdaysController.php
│   │   ├── Controller.php
│   │   ├── InfoController.php
│   │   ├── NewsController.php
│   │   ├── OrderController.php
│   │   ├── PhoneBookController.php
│   │   ├── Redmine
│   │   │   ├── AttachmentController.php
│   │   │   ├── CustomFieldController.php
│   │   │   ├── IssueController.php
│   │   │   └── ProjectController.php
│   │   └── SopController.php
│   └── Middleware
│       └── WindowsAuthentication.php
├── Models
│   ├── AttachmentLink.php
│   ├── Attachment.php
│   ├── Building.php
│   ├── CorpusDept.php
│   ├── Corpus.php
│   ├── Dept.php
│   ├── DeptType.php
│   ├── DeptWorker.php
│   ├── Info.php
│   ├── InfoType.php
│   ├── News.php
│   ├── OrderStorage.php
│   ├── Phone.php
│   ├── Redmine
│   │   ├── RedmineAttachment.php
│   │   ├── RedmineCustomField.php
│   │   ├── RedmineCustomValue.php
│   │   ├── RedmineEnabledModule.php
│   │   ├── RedmineEnumeration.php
│   │   ├── RedmineIssue.php
│   │   ├── RedmineIssueStatus.php
│   │   ├── RedmineJournalDetail.php
│   │   ├── RedmineJournal.php
│   │   ├── RedmineMember.php
│   │   ├── RedmineMemberRole.php
│   │   ├── RedmineModel.php
│   │   ├── RedmineProject.php
│   │   ├── RedmineRole.php
│   │   ├── RedmineTracker.php
│   │   └── RedmineUser.php
│   ├── Session.php
│   ├── Sop.php
│   ├── SsoUser.php
│   ├── User.php
│   ├── WorkerPhone.php
│   └── Worker.php
├── Observers
│   └── NewsObserver.php
├── Providers
│   ├── AppServiceProvider.php
│   └── Filament
│       └── AdminPanelProvider.php
└── Services
    ├── ActiveDirectoryService.php
    └── Redmine
        ├── RedmineAttachmentService.php
        ├── RedmineCustomFieldService.php
        ├── RedmineIssueService.php
        ├── RedmineProjectService.php
        └── RedmineUserService.php

47 directories, 119 files
```

## Роуты
```

  GET|HEAD   / ............................................................................................................................................ home › NewsController@index
  GET|HEAD   admin/attachments ........................................... filament.admin.resources.attachments.index › App\Filament\Resources\AttachmentResource\Pages\ListAttachments
  GET|HEAD   admin/attachments/create .................................. filament.admin.resources.attachments.create › App\Filament\Resources\AttachmentResource\Pages\CreateAttachment
  GET|HEAD   admin/attachments/{record}/edit ............................... filament.admin.resources.attachments.edit › App\Filament\Resources\AttachmentResource\Pages\EditAttachment
  GET|HEAD   admin/buildings ................................................... filament.admin.resources.buildings.index › App\Filament\Resources\BuildingResource\Pages\ListBuildings
  GET|HEAD   admin/buildings/create .......................................... filament.admin.resources.buildings.create › App\Filament\Resources\BuildingResource\Pages\CreateBuilding
  GET|HEAD   admin/buildings/{record}/edit ....................................... filament.admin.resources.buildings.edit › App\Filament\Resources\BuildingResource\Pages\EditBuilding
  GET|HEAD   admin/corpus-depts ................................................ filament.admin.resources.corpus-depts.index › App\Filament\Resources\CorpusResource\Pages\ListCorpuses
  GET|HEAD   admin/corpus-depts/create ........................................ filament.admin.resources.corpus-depts.create › App\Filament\Resources\CorpusResource\Pages\CreateCorpus
  GET|HEAD   admin/corpus-depts/{record}/edit ..................................... filament.admin.resources.corpus-depts.edit › App\Filament\Resources\CorpusResource\Pages\EditCorpus
  GET|HEAD   admin/corpuses ........................................................ filament.admin.resources.corpuses.index › App\Filament\Resources\CorpusResource\Pages\ListCorpuses
  GET|HEAD   admin/corpuses/create ................................................ filament.admin.resources.corpuses.create › App\Filament\Resources\CorpusResource\Pages\CreateCorpus
  GET|HEAD   admin/corpuses/{record}/edit ............................................. filament.admin.resources.corpuses.edit › App\Filament\Resources\CorpusResource\Pages\EditCorpus
  GET|HEAD   admin/dept-types ................................................. filament.admin.resources.dept-types.index › App\Filament\Resources\DeptTypeResource\Pages\ListDeptTypes
  GET|HEAD   admin/dept-types/create ........................................ filament.admin.resources.dept-types.create › App\Filament\Resources\DeptTypeResource\Pages\CreateDeptType
  GET|HEAD   admin/dept-types/{record}/edit ..................................... filament.admin.resources.dept-types.edit › App\Filament\Resources\DeptTypeResource\Pages\EditDeptType
  GET|HEAD   admin/dept-workers ......................................... filament.admin.resources.dept-workers.index › App\Filament\Resources\DeptWorkerResource\Pages\ListDeptWorkers
  GET|HEAD   admin/dept-workers/create ................................ filament.admin.resources.dept-workers.create › App\Filament\Resources\DeptWorkerResource\Pages\CreateDeptWorker
  GET|HEAD   admin/dept-workers/{record}/edit ............................. filament.admin.resources.dept-workers.edit › App\Filament\Resources\DeptWorkerResource\Pages\EditDeptWorker
  GET|HEAD   admin/depts ................................................................... filament.admin.resources.depts.index › App\Filament\Resources\DeptResource\Pages\ListDepts
  GET|HEAD   admin/depts/create .......................................................... filament.admin.resources.depts.create › App\Filament\Resources\DeptResource\Pages\CreateDept
  GET|HEAD   admin/depts/{record}/edit ....................................................... filament.admin.resources.depts.edit › App\Filament\Resources\DeptResource\Pages\EditDept
  GET|HEAD   admin/info-types ................................................. filament.admin.resources.info-types.index › App\Filament\Resources\InfoTypeResource\Pages\ListInfoTypes
  GET|HEAD   admin/info-types/create ........................................ filament.admin.resources.info-types.create › App\Filament\Resources\InfoTypeResource\Pages\CreateInfoType
  GET|HEAD   admin/info-types/{record}/edit ..................................... filament.admin.resources.info-types.edit › App\Filament\Resources\InfoTypeResource\Pages\EditInfoType
  GET|HEAD   admin/infos .................................................................... filament.admin.resources.infos.index › App\Filament\Resources\InfoResource\Pages\ListInfo
  GET|HEAD   admin/infos/create .......................................................... filament.admin.resources.infos.create › App\Filament\Resources\InfoResource\Pages\CreateInfo
  GET|HEAD   admin/infos/{record}/edit ....................................................... filament.admin.resources.infos.edit › App\Filament\Resources\InfoResource\Pages\EditInfo
  GET|HEAD   admin/news ...................................................................... filament.admin.resources.news.index › App\Filament\Resources\NewsResource\Pages\ListNews
  GET|HEAD   admin/news/create ............................................................ filament.admin.resources.news.create › App\Filament\Resources\NewsResource\Pages\CreateNews
  GET|HEAD   admin/news/{record}/edit ......................................................... filament.admin.resources.news.edit › App\Filament\Resources\NewsResource\Pages\EditNews
  GET|HEAD   admin/order-storages ................................. filament.admin.resources.order-storages.index › App\Filament\Resources\OrderStorageResource\Pages\ListOrderStorages
  GET|HEAD   admin/order-storages/create ........................ filament.admin.resources.order-storages.create › App\Filament\Resources\OrderStorageResource\Pages\CreateOrderStorage
  GET|HEAD   admin/order-storages/{record}/edit ..................... filament.admin.resources.order-storages.edit › App\Filament\Resources\OrderStorageResource\Pages\EditOrderStorage
  GET|HEAD   admin/phones ............................................................... filament.admin.resources.phones.index › App\Filament\Resources\PhoneResource\Pages\ListPhones
  GET|HEAD   admin/phones/create ...................................................... filament.admin.resources.phones.create › App\Filament\Resources\PhoneResource\Pages\CreatePhone
  GET|HEAD   admin/phones/{record}/edit ................................................... filament.admin.resources.phones.edit › App\Filament\Resources\PhoneResource\Pages\EditPhone
  GET|HEAD   admin/sops ....................................................................... filament.admin.resources.sops.index › App\Filament\Resources\SopResource\Pages\ListSops
  GET|HEAD   admin/sops/create .............................................................. filament.admin.resources.sops.create › App\Filament\Resources\SopResource\Pages\CreateSop
  GET|HEAD   admin/sops/{record}/edit ........................................................... filament.admin.resources.sops.edit › App\Filament\Resources\SopResource\Pages\EditSop
  GET|HEAD   admin/worker-phones ..................................... filament.admin.resources.worker-phones.index › App\Filament\Resources\WorkerPhoneResource\Pages\ListWorkerPhones
  GET|HEAD   admin/worker-phones/create ............................ filament.admin.resources.worker-phones.create › App\Filament\Resources\WorkerPhoneResource\Pages\CreateWorkerPhone
  GET|HEAD   admin/worker-phones/{record}/edit ......................... filament.admin.resources.worker-phones.edit › App\Filament\Resources\WorkerPhoneResource\Pages\EditWorkerPhone
  GET|HEAD   admin/workers ........................................................... filament.admin.resources.workers.index › App\Filament\Resources\WorkerResource\Pages\ListWorkers
  GET|HEAD   admin/workers/create .................................................. filament.admin.resources.workers.create › App\Filament\Resources\WorkerResource\Pages\CreateWorker
  GET|HEAD   admin/workers/{record}/edit ............................................... filament.admin.resources.workers.edit › App\Filament\Resources\WorkerResource\Pages\EditWorker
  GET|HEAD   birthdays .......................................................................................................................... birthdays › BirthdaysController@index
  GET|HEAD   directory .......................................................................................................................... directory › PhoneBookController@index
  GET|HEAD   directory/search ........................................................................................................... directory.search › PhoneBookController@search
  GET|HEAD   download/{model}/{id}/{attachment} ............................................................................................... download.attachment › routes/web.php:44
  GET|HEAD   info ......................................................................................................................................... info › InfoController@index
  GET|HEAD   new_worker .................................................................................................................................................... new_worker
  GET|HEAD   orders .................................................................................................................................... orders › OrderController@index
  GET|HEAD   redmine/appeals ........................................................................................................ redmine.appeals › Redmine\ProjectController@index
  GET|HEAD   redmine/attachments/{id}/download ................................................................... redmine.attachments.download › Redmine\AttachmentController@download
  GET|HEAD   redmine/custom-fields ........................................................................................ redmine.custom-fields › Redmine\CustomFieldController@index
  POST       redmine/issues ...................................................................................................... redmine.issues.store › Redmine\IssueController@store
  GET|HEAD   redmine/issues/{id} ................................................................................................... redmine.issues.show › Redmine\IssueController@show
  POST       redmine/issues/{id}/comment ..................................................................................... redmine.issues.comment › Redmine\IssueController@comment
  GET|HEAD   redmine/projects/{id} ............................................................................................. redmine.projects.show › Redmine\ProjectController@show
  GET|HEAD   redmine/trackers ............................................................................................... redmine.trackers › Redmine\CustomFieldController@trackers
  GET|HEAD   search ............................................................................................................................... home.search › NewsController@search
  GET|HEAD   sop ............................................................................................................................................ sop › SopController@index

                                                                                                                                                                    Showing [63] routes

```

## Модели
```
AttachmentLink.php
Attachment.php
Building.php
CorpusDept.php
Corpus.php
Dept.php
DeptType.php
DeptWorker.php
Info.php
InfoType.php
News.php
OrderStorage.php
Phone.php
Redmine
Session.php
Sop.php
SsoUser.php
User.php
WorkerPhone.php
Worker.php
```

## Сервисы
```
app/Services/ActiveDirectoryService.php
app/Services/Redmine/RedmineAttachmentService.php
app/Services/Redmine/RedmineCustomFieldService.php
app/Services/Redmine/RedmineIssueService.php
app/Services/Redmine/RedmineProjectService.php
app/Services/Redmine/RedmineUserService.php
```

## Actions
```
```

## Jobs
```
```

## Events / Listeners
```
```
