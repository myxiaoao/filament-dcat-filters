# GeoLocationFilter

基于经纬度坐标按地理位置距离筛选记录。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\GeoLocationFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            GeoLocationFilter::make('location')
                ->latitude('lat')
                ->longitude('lng')
                ->radius(10, 'km'),
        ]);
}
```

## 列配置

### 默认列名

默认使用 `latitude` 和 `longitude` 列。

### 自定义列名

```php
GeoLocationFilter::make('location')
    ->latitude('lat')
    ->longitude('lng')
```

### 同时设置两个列

```php
GeoLocationFilter::make('location')
    ->coordinates('store_lat', 'store_lng')
```

## 半径配置

### 默认半径

```php
GeoLocationFilter::make('location')
    ->radius(10)  // 默认10公里
```

### 指定单位

```php
GeoLocationFilter::make('location')
    ->radius(5, 'mi')  // 5英里
```

## 距离单位

### 公里 (默认)

```php
GeoLocationFilter::make('location')
    ->kilometers()
```

### 英里

```php
GeoLocationFilter::make('location')
    ->miles()
```

### 米

```php
GeoLocationFilter::make('location')
    ->meters()
```

## 中心点

设置搜索的默认中心点:

```php
GeoLocationFilter::make('location')
    ->center(39.9042, 116.4074)  // 北京
    ->radius(25, 'km')
```

## 完整示例

```php
GeoLocationFilter::make('store_location')
    ->label('附近位置')
    ->coordinates('store_lat', 'store_lng')
    ->radius(10, 'km')
    ->center(31.2304, 121.4737)  // 上海
    ->columnSpan(2),
```

## 表单字段

过滤器显示三个输入字段:
- **纬度**: 十进制度数 (例如 39.9042)
- **经度**: 十进制度数 (例如 116.4074)
- **半径**: 配置单位的距离值

## 工作原理

过滤器使用 **Haversine 公式** 计算地球上两点之间的大圆距离:

```
a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlng/2)
c = 2 × atan2(√a, √(1-a))
d = R × c
```

其中:
- `R` = 地球半径 (6,371 公里)
- `lat1`, `lng1` = 中心点坐标
- `lat2`, `lng2` = 记录坐标
- `d` = 距离 (公里)

## 使用场景

- 门店定位器 (查找X公里内的门店)
- 配送区域筛选
- 活动地点搜索
- 房产区域搜索
- 服务商可用性查询

## 数据库要求

表必须包含:
- 纬度列 (decimal，通常 -90 到 90)
- 经度列 (decimal，通常 -180 到 180)

迁移示例:

```php
Schema::create('stores', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('latitude', 10, 8);
    $table->decimal('longitude', 11, 8);
    $table->timestamps();
});
```

## 性能优化建议

处理大数据集时的优化建议:

1. **添加空间索引** (MySQL 8.0+):
   ```sql
   ALTER TABLE stores ADD SPATIAL INDEX(location);
   ```

2. **使用边界框预过滤**:
   Haversine 计算是 CPU 密集型的，考虑先添加粗略的边界框过滤。

3. **缓存结果** 用于常见搜索区域。

## 数据库兼容性

Haversine 公式 SQL 兼容:
- MySQL 5.7+
- MariaDB 10.2+
- PostgreSQL 9.0+
- SQLite (需启用数学函数)

对于高级地理空间查询，建议使用:
- MySQL 内置空间函数 (`ST_Distance_Sphere`)
- PostgreSQL 的 PostGIS 扩展
