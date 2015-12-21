# nashCrm

环境要求:php5.2,Mysql5.0<br>
表结构:crm.sql<br>

##安装教程：<br>
第一步：把php文件拷贝到www目录下；<br>
第二步：把crm.sql文件导入到mysql数据库中；<br>
第三步：修改app/config(config-dev)/config.php文件里的数据库信息和目录信息，<br>

##项目说明：<br>
`app`：后台逻辑处理部分<br>
`crm`：前端页面部分<br>
`database`：数据库模块<br>
`framework`：后台基础框架<br>

##特别说明：<br>
`app/index.php`为入口文件，控制着加载不同的配置文件，以满足开发&生产环境的快速切换<br>

##特别感谢：<br>
@ 此CRM系统的静态页面基于<a href="http://materializecss.com/">materializecss</a>构建，在此特别感谢materializecss开发者提供相关框架<br>
@ 此系统在<a href="nash.work">纳什空间</a>中被广泛的使用，并且相关人员积极给出建议，在此表示十分感谢他们的积极参与<br>

##开发人员：<br>
E-mail:zhaoguang@nash.work<br>
